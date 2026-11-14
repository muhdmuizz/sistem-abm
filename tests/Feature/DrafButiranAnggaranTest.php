<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\ButiranAnggaran;
use App\Models\Fasa;
use App\Models\HasilPengiraan;
use App\Models\Kitaran;
use App\Models\PemetaanOs;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\RujukanOs;
use App\Models\TempatBertugas;
use App\Models\TetapanAksesPtj;
use App\Models\User;
use App\Services\DrafButiranAnggaran;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DrafButiranAnggaranTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Ptj $owner;
    private AktivitiKewangan $activity;
    private Kitaran $cycle;
    private Fasa $phase;
    private RujukanOs $os;
    private TempatBertugas $workplace;
    private Bidaan $bid;
    private Anggaran $estimate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2028-05-15 12:00:00', 'Asia/Kuala_Lumpur'));
        $this->owner = Ptj::create(['kod_ptj' => 'DRAFT-001', 'nama' => 'Draft fixture', 'kategori' => 'PPD']);
        $this->activity = AktivitiKewangan::create(['nama' => 'Draft activity fixture']);
        $this->cycle = Kitaran::create(['tahun' => 2028]);
        $this->workplace = TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => 'Draft sector', 'jenis' => 'sektor']);
        $this->os = RujukanOs::create(['kod_objek' => '14000', 'butiran_perbelanjaan' => 'Draft OS fixture']);
        PemetaanOs::create(['aktiviti_kewangan_id' => $this->activity->id, 'rujukan_os_id' => $this->os->id,
            'singkatan_sumber' => 'Fixture', 'butiran_asal' => 'Fixture', 'status_sumber' => 'Aktif']);
        foreach ([null, $this->os->id] as $osId) {
            TetapanAksesPtj::create(['kategori_ptj' => 'PPD', 'aktiviti_kewangan_id' => $this->activity->id,
                'rujukan_os_id' => $osId, 'dibenarkan' => true]);
        }
        $this->phase = Fasa::forceCreate(['kitaran_id' => $this->cycle->id, 'tahun_kitaran' => 2028,
            'jenis' => 'bidaan_awal', 'tarikh_mula' => '2028-05-01', 'tarikh_tamat' => '2028-05-31',
            'jenis_skop' => 'global', 'dibuka' => true, 'pernah_dibuka' => true]);
        $this->phase->skop()->create(['jenis_skop' => 'global']);
        $this->operator = User::factory()->create();
        $this->operator->forceFill(['is_active' => true, 'ptj_id' => $this->owner->id,
            'tempat_bertugas_id' => $this->workplace->id, 'google_subject' => 'verified-draft-fixture'])->save();
        $this->draftRole();
        $this->operator->refresh();
        $this->actingAs($this->operator)->withSession([
            'abm_access_revision' => $this->operator->access_revision,
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp,
        ]);
        $this->bid = Bidaan::forceCreate(['ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id,
            'kitaran_id' => $this->cycle->id, 'diaktifkan' => true, 'semakan' => 1])->fresh();
        $this->estimate = Anggaran::forceCreate(['bidaan_id' => $this->bid->id, 'ptj_id' => $this->owner->id,
            'rujukan_os_id' => $this->os->id, 'diaktifkan' => true, 'semakan' => 1])->fresh();
    }

    private function draftRole(?int $workplaceId = null): void
    {
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::PenyediaAnggaran,
            'ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id,
            'tempat_bertugas_id' => $workplaceId]);
    }

    private function draftInput(?stdClass $data = null, ?ButiranAnggaran $source = null, array $replace = []): array
    {
        $input = ['tempat_bertugas_id' => $source?->tempat_bertugas_id ?? $this->workplace->id,
            'data_sumber' => $data ?? (object) [], 'semakan_bidaan' => $this->bid->fresh()->semakan,
            'semakan_anggaran' => $this->estimate->fresh()->semakan];
        if ($source !== null) {
            $input['semakan_butiran'] = $source->fresh()->semakan;
        }
        return array_replace($input, $replace);
    }

    private function draftSave(?stdClass $data = null, ?ButiranAnggaran $source = null, array $replace = []): ButiranAnggaran
    {
        return app(DrafButiranAnggaran::class)->simpan($this->estimate->id, $source?->id, $this->draftInput($data, $source, $replace));
    }

    private function draftDenied(callable $operation, int $code): void
    {
        try {
            $operation();
            $this->fail('Expected draft write rejection.');
        } catch (HttpException $exception) {
            $this->assertSame($code, $exception->getStatusCode());
        } catch (ModelNotFoundException) {
            $this->assertSame(404, $code);
        } catch (ValidationException) {
            $this->assertSame(422, $code);
        }
    }

    private function draftAuditFailure(): void
    {
        DB::unprepared(<<<'SQL'
CREATE FUNCTION draft_test_audit_failure() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN RAISE EXCEPTION 'Draft audit unavailable'; END;
$$;
CREATE TRIGGER draft_test_audit_failure BEFORE INSERT ON rekod_audit
FOR EACH ROW EXECUTE FUNCTION draft_test_audit_failure();
SQL);
    }

    public function test_empty_incomplete_source_is_persisted_with_actual_actor_workplace_and_no_outputs(): void
    {
        $source = $this->draftSave();
        $this->assertSame($this->estimate->id, $source->anggaran_id);
        $this->assertSame($this->owner->id, $source->ptj_id);
        $this->assertSame($this->workplace->id, $source->tempat_bertugas_id);
        $this->assertEquals((object) [], $source->data_sumber);
        $this->assertSame(1, $source->semakan);
        $this->assertSame(2, $this->estimate->fresh()->semakan);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertSame(StatusBidaan::DalamPenyediaan, $this->bid->fresh()->status);
        $audit = RekodAudit::sole();
        $this->assertSame($this->operator->id, $audit->pelaku_id);
        $this->assertSame(Peranan::PenyediaAnggaran, $audit->peranan);
        $this->assertSame($this->workplace->id, $audit->tempat_bertugas_id);
        $this->assertSame($this->cycle->id, $audit->kitaran_id);
        $this->assertSame('source.draft_created', $audit->tindakan);
        $this->assertSame((string) $source->id, $audit->id_entiti);
        $this->assertSame('{}', $audit->selepas['data_sumber_json']);
        $this->assertNull($audit->versi_penghantaran);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
    }

    public function test_edit_retains_source_identity_and_audits_before_after_without_changing_sibling_sources(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'first']);
        $sibling = $this->draftSave((object) ['fixture' => 'same officer']);
        $edited = $this->draftSave((object) ['fixture' => 'updated'], $source);
        $this->assertSame($source->id, $edited->id);
        $this->assertSame('updated', $edited->data_sumber->fixture);
        $this->assertSame('same officer', $sibling->fresh()->data_sumber->fixture);
        $this->assertSame(2, $edited->semakan);
        $this->assertSame(1, $sibling->fresh()->semakan);
        $this->assertSame(4, $this->bid->fresh()->semakan);
        $audit = RekodAudit::orderByDesc('id')->first();
        $this->assertSame('source.draft_updated', $audit->tindakan);
        $this->assertSame('first', json_decode($audit->sebelum['data_sumber_json'])->fixture);
        $this->assertSame('updated', json_decode($audit->selepas['data_sumber_json'])->fixture);
        $this->assertDatabaseCount('butiran_anggaran', 2);
    }

    public function test_identical_values_create_distinct_sources_and_original_create_retry_conflicts(): void
    {
        $input = $this->draftInput((object) ['fixture' => 'same']);
        $first = app(DrafButiranAnggaran::class)->simpan($this->estimate->id, null, $input);
        $this->draftDenied(fn () => app(DrafButiranAnggaran::class)->simpan($this->estimate->id, null, $input), 409);
        $second = $this->draftSave((object) ['fixture' => 'same']);
        $this->assertNotSame($first->id, $second->id);
        $this->assertDatabaseCount('butiran_anggaran', 2);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    public function test_unchanged_object_key_order_is_a_noop_after_rechecking_revisions_and_authority(): void
    {
        $source = $this->draftSave((object) ['z' => (object) ['b' => 2, 'a' => 1], 'a' => 'text']);
        $same = $this->draftSave((object) ['a' => 'text', 'z' => (object) ['a' => 1, 'b' => 2]], $source);
        $this->assertSame($source->id, $same->id);
        $this->assertSame(1, $same->semakan);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
        $this->phase->forceFill(['dibuka' => false])->save();
        $this->draftDenied(fn () => $this->draftSave($same->data_sumber, $same), 403);
    }

    public function test_all_three_revisions_are_checked_and_conflicts_do_not_overwrite(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'keep']);
        foreach (['semakan_bidaan', 'semakan_anggaran', 'semakan_butiran'] as $key) {
            $this->draftDenied(fn () => $this->draftSave((object) ['fixture' => 'overwrite'], $source, [$key => 0]), 409);
        }
        $this->assertSame('keep', $source->fresh()->data_sumber->fixture);
        $this->assertSame(1, $source->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_own_workplace_user_cannot_take_or_move_another_workplaces_source(): void
    {
        $other = TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => 'Another sector', 'jenis' => 'sektor']);
        $foreign = $this->draftSave((object) ['fixture' => 'other workplace'], replace: ['tempat_bertugas_id' => $other->id]);
        $own = $this->draftSave((object) ['fixture' => 'own']);
        $this->operator->penugasanPeranan()->delete();
        $this->draftRole($this->workplace->id);
        $this->draftDenied(fn () => $this->draftSave((object) ['fixture' => 'take over'], $foreign,
            ['tempat_bertugas_id' => $this->workplace->id]), 403);
        $this->draftDenied(fn () => $this->draftSave((object) ['fixture' => 'move away'], $own,
            ['tempat_bertugas_id' => $other->id]), 403);
        $this->draftDenied(fn () => $this->draftSave((object) [], replace: ['tempat_bertugas_id' => $other->id]), 403);
        $this->assertSame($other->id, $foreign->fresh()->tempat_bertugas_id);
        $this->assertSame($this->workplace->id, $own->fresh()->tempat_bertugas_id);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    public function test_ptj_level_preparer_can_change_to_another_permitted_workplace_with_history(): void
    {
        $source = $this->draftSave();
        $other = TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => 'Another sector', 'jenis' => 'sektor']);
        $edited = $this->draftSave(source: $source, replace: ['tempat_bertugas_id' => $other->id]);
        $this->assertSame($source->id, $edited->id);
        $this->assertSame($other->id, $edited->tempat_bertugas_id);
        $audit = RekodAudit::orderByDesc('id')->first();
        $this->assertSame($this->workplace->id, $audit->sebelum['tempat_bertugas_id']);
        $this->assertSame($other->id, $audit->selepas['tempat_bertugas_id']);
    }

    public function test_cross_ptj_workplace_and_wrong_source_parent_are_rejected(): void
    {
        $otherPtj = Ptj::create(['kod_ptj' => 'DRAFT-002', 'nama' => 'Other PTJ', 'kategori' => 'PPD']);
        $otherWorkplace = TempatBertugas::create(['ptj_id' => $otherPtj->id, 'nama' => 'Other parent sector', 'jenis' => 'sektor']);
        $this->draftDenied(fn () => $this->draftSave(replace: ['tempat_bertugas_id' => $otherWorkplace->id]), 403);
        $otherOs = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Other fixture']);
        $otherEstimate = Anggaran::forceCreate(['bidaan_id' => $this->bid->id, 'ptj_id' => $this->owner->id,
            'rujukan_os_id' => $otherOs->id, 'diaktifkan' => true]);
        $source = ButiranAnggaran::forceCreate(['anggaran_id' => $otherEstimate->id, 'ptj_id' => $this->owner->id,
            'tempat_bertugas_id' => $this->workplace->id]);
        $this->draftDenied(fn () => app(DrafButiranAnggaran::class)->simpan($this->estimate->id, $source->id,
            $this->draftInput(source: $source)), 404);
        $this->assertSame($otherEstimate->id, $source->fresh()->anggaran_id);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    #[DataProvider('draftNonPreparerRoles')]
    public function test_other_roles_do_not_get_source_write_rights(Peranan $role): void
    {
        $this->operator->penugasanPeranan()->delete();
        $attributes = ['peranan' => $role];
        if (in_array($role, [Peranan::KetuaBidaan, Peranan::PenyeliaBidaan, Peranan::PenyediaBidaan], true)) {
            $attributes['aktiviti_kewangan_id'] = $this->activity->id;
        }
        if ($role === Peranan::PenyediaBidaan) {
            $attributes['ptj_id'] = $this->owner->id;
        }
        $this->operator->penugasanPeranan()->create($attributes);
        $this->draftDenied(fn () => $this->draftSave(), 403);
        $this->assertDatabaseCount('butiran_anggaran', 0);
    }

    public static function draftNonPreparerRoles(): array
    {
        return [[Peranan::PenyediaBidaan], [Peranan::PenyeliaBidaan], [Peranan::KetuaBidaan], [Peranan::PemantauBidaan]];
    }

    public function test_current_central_grants_phase_and_actor_are_rechecked_for_draft_saves(): void
    {
        $input = $this->draftInput();
        $this->phase->forceFill(['dibuka' => false])->save();
        $this->draftDenied(fn () => $this->draftSave(), 403);
        $this->phase->forceFill(['dibuka' => true])->save();
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => false]);
        $this->draftDenied(fn () => $this->draftSave(), 403);
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => true]);
        $this->operator->increment('access_revision');
        $this->draftDenied(fn () => app(DrafButiranAnggaran::class)->simpan($this->estimate->id, null, $input), 403);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->assertDatabaseCount('butiran_anggaran', 0);
    }

    public function test_expired_session_cannot_save_a_draft(): void
    {
        session()->put('abm_last_activity', now()->timestamp - 1800);
        $this->draftDenied(fn () => $this->draftSave(), 403);
        $this->assertDatabaseCount('butiran_anggaran', 0);
    }

    public function test_inactive_parents_submitted_and_finalised_states_are_read_only(): void
    {
        $this->bid->forceFill(['diaktifkan' => false])->save();
        $this->draftDenied(fn () => $this->draftSave(), 409);
        $this->bid->forceFill(['diaktifkan' => true])->save();
        $this->estimate->forceFill(['diaktifkan' => false])->save();
        $this->draftDenied(fn () => $this->draftSave(), 409);
        $this->estimate->forceFill(['diaktifkan' => true])->save();
        foreach ([StatusBidaan::DalamSemakan, StatusBidaan::Muktamad] as $status) {
            $this->bid->forceFill(['status' => $status])->save();
            $this->draftDenied(fn () => $this->draftSave(), 409);
        }
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_returned_bid_accepts_drafts_without_changing_the_return_status_or_submission_version(): void
    {
        $this->bid->forceFill(['status' => StatusBidaan::Dikembalikan])->save();
        $source = $this->draftSave((object) ['fixture' => 'correction']);
        $this->assertSame(StatusBidaan::Dikembalikan, $this->bid->fresh()->status);
        $this->assertSame($source->id, $this->estimate->butiran()->sole()->id);
        $this->assertNull(RekodAudit::sole()->versi_penghantaran);
    }

    public function test_exact_decimal_text_and_nested_json_types_survive_save_and_audit(): void
    {
        $source = $this->draftSave((object) ['decimal_fixture' => '0.123456789012345678901234567890',
            'integer_fixture' => PHP_INT_MAX, 'null_fixture' => null, 'bool_fixture' => false,
            'list_fixture' => ['x', 2, (object) []], 'object_fixture' => (object) []]);
        $this->assertSame('0.123456789012345678901234567890', $source->data_sumber->decimal_fixture);
        $this->assertSame(PHP_INT_MAX, $source->data_sumber->integer_fixture);
        $this->assertFalse($source->data_sumber->bool_fixture);
        $this->assertInstanceOf(stdClass::class, $source->data_sumber->object_fixture);
        $this->assertInstanceOf(stdClass::class, $source->data_sumber->list_fixture[2]);
        $auditData = json_decode(RekodAudit::sole()->selepas['data_sumber_json']);
        $this->assertSame($source->data_sumber->decimal_fixture, $auditData->decimal_fixture);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
    }

    #[DataProvider('draftInvalidSources')]
    public function test_non_json_float_and_sensitive_source_inputs_leave_no_partial_state(mixed $data): void
    {
        $this->draftDenied(fn () => app(DrafButiranAnggaran::class)->simpan($this->estimate->id, null,
            array_replace($this->draftInput(), ['data_sumber' => $data])), 422);
        $this->assertSame(1, $this->bid->fresh()->semakan);
        $this->assertSame(1, $this->estimate->fresh()->semakan);
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public static function draftInvalidSources(): array
    {
        return [
            'list root' => [[]],
            'null root' => [null],
            'decimal float' => [(object) ['fixture' => 0.1]],
            'infinite float' => [(object) ['fixture' => INF]],
            'non json object' => [(object) ['fixture' => new \DateTimeImmutable('2028-01-01')]],
            'non list PHP array' => [(object) ['fixture' => ['key' => 'value']]],
            'credential field' => [(object) ['nested' => (object) ['accessToken' => 'fixture']]],
            'credential hyphen field' => [(object) ['api-key' => 'fixture']],
            'invalid utf8' => [(object) ['fixture' => "\xB1\x31"]],
        ];
    }

    public function test_recursive_source_object_is_rejected_without_recursing_forever(): void
    {
        $data = new stdClass;
        $data->self = $data;
        $this->draftDenied(fn () => $this->draftSave($data), 422);
        $this->assertDatabaseCount('butiran_anggaran', 0);
    }

    public function test_metadata_mass_assignment_missing_revisions_and_invalid_workplace_are_rejected(): void
    {
        foreach ([
            ['ptj_id' => $this->owner->id], ['amaun' => '9999'], ['status' => 'lengkap'],
            ['semakan_bidaan' => null], ['semakan_anggaran' => -1], ['tempat_bertugas_id' => 0],
            ['semakan_butiran' => 9],
        ] as $bad) {
            $this->draftDenied(fn () => $this->draftSave(replace: $bad), 422);
        }
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_rows_with_generated_outputs_cannot_be_changed_by_draft_only_saving(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'calculated source']);
        $output = HasilPengiraan::forceCreate(['butiran_anggaran_id' => $source->id,
            'semakan_sumber' => $source->semakan, 'turutan' => 1, 'amaun' => '123.456789'])->fresh();
        $same = $this->draftSave($source->data_sumber, $source);
        $this->assertSame($source->id, $same->id);
        $this->draftDenied(fn () => $this->draftSave((object) ['fixture' => 'changed'], $source), 409);
        $this->assertSame('calculated source', $source->fresh()->data_sumber->fixture);
        $this->assertSame('123.456789', $output->fresh()->amaun);
        $this->assertSame(1, $source->fresh()->semakan);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_actual_audit_insert_failure_rolls_back_creation_and_both_parent_revisions(): void
    {
        $this->draftAuditFailure();
        try {
            $this->draftSave();
            $this->fail('Audit failure ignored.');
        } catch (QueryException $exception) {
            $this->assertSame('P0001', (string) $exception->getCode());
        }
        $this->assertSame(1, $this->bid->fresh()->semakan);
        $this->assertSame(1, $this->estimate->fresh()->semakan);
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_actual_audit_insert_failure_restores_existing_source_and_workplace_change(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'keep']);
        $other = TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => 'Another sector', 'jenis' => 'sektor']);
        $this->draftAuditFailure();
        try {
            $this->draftSave((object) ['fixture' => 'change'], $source, ['tempat_bertugas_id' => $other->id]);
            $this->fail('Audit failure ignored.');
        } catch (QueryException $exception) {
            $this->assertSame('P0001', (string) $exception->getCode());
        }
        $this->assertSame('keep', $source->fresh()->data_sumber->fixture);
        $this->assertSame($this->workplace->id, $source->fresh()->tempat_bertugas_id);
        $this->assertSame(1, $source->fresh()->semakan);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertSame(2, $this->estimate->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_superadmin_uses_real_identity_and_reasoned_override_without_ignoring_state(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::Superadmin]);
        $this->phase->forceFill(['dibuka' => false])->save();
        TetapanAksesPtj::query()->update(['dibenarkan' => false]);
        $this->draftDenied(fn () => $this->draftSave(), 422);
        $source = $this->draftSave(replace: ['reason' => 'Pembetulan pentadbiran yang diluluskan.']);
        $audit = RekodAudit::sole();
        $this->assertSame($this->operator->id, $audit->pelaku_id);
        $this->assertSame(Peranan::Superadmin, $audit->peranan);
        $this->assertTrue($audit->selepas['pintasan']['fasa']);
        $this->assertTrue($audit->selepas['pintasan']['kebenaran_pusat']);
        $this->bid->forceFill(['status' => StatusBidaan::Muktamad])->save();
        $this->draftDenied(fn () => $this->draftSave((object) ['fixture' => 'change'], $source,
            ['reason' => 'Pentadbiran.']), 409);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_excluded_and_unmapped_os_remain_unavailable_even_to_superadmin(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::Superadmin]);
        $this->os->update(['dikecualikan' => true]);
        $this->draftDenied(fn () => $this->draftSave(replace: ['reason' => 'Semak katalog.']), 404);
        $this->os->update(['dikecualikan' => false]);
        PemetaanOs::query()->delete();
        $this->draftDenied(fn () => $this->draftSave(replace: ['reason' => 'Semak pemetaan.']), 404);
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }
    private function draftBatchInput(array $rows, array $replace = []): array
    {
        return array_replace(['semakan_bidaan' => $this->bid->fresh()->semakan,
            'semakan_anggaran' => $this->estimate->fresh()->semakan, 'rekod' => $rows], $replace);
    }

    private function draftBatchRow(string $value, ?ButiranAnggaran $source = null, array $replace = []): array
    {
        $row = ['tempat_bertugas_id' => $this->workplace->id, 'data_sumber' => (object) ['fixture' => $value]];
        if ($source) {
            $row += ['butiran_id' => $source->id, 'semakan_butiran' => $source->fresh()->semakan];
        }
        return array_replace($row, $replace);
    }

    private function draftBatchSave(array $rows, array $replace = []): array
    {
        return app(DrafButiranAnggaran::class)->simpanBanyak($this->estimate->id, $this->draftBatchInput($rows, $replace));
    }

    public function test_batch_mixed_create_edit_and_noop_retains_independent_ids_order_and_exact_values(): void
    {
        $existing = $this->draftSave((object) ['fixture' => 'before']);
        $noop = $this->draftSave((object) ['fixture' => 'same']);
        $rows = $this->draftBatchSave([
            $this->draftBatchRow('0.12345678901234567890'),
            $this->draftBatchRow('after', $existing),
            $this->draftBatchRow('same', $noop),
            $this->draftBatchRow('0.12345678901234567890'),
        ]);
        $this->assertSame($existing->id, $rows[1]->id);
        $this->assertSame($noop->id, $rows[2]->id);
        $this->assertNotSame($rows[0]->id, $rows[3]->id);
        $this->assertSame('0.12345678901234567890', $rows[0]->fresh()->data_sumber->fixture);
        $this->assertSame('after', $existing->fresh()->data_sumber->fixture);
        $this->assertSame(1, $noop->fresh()->semakan);
        $this->assertSame(6, $this->bid->fresh()->semakan);
        $this->assertSame(6, $this->estimate->fresh()->semakan);
        $this->assertDatabaseCount('butiran_anggaran', 4);
        $this->assertDatabaseCount('rekod_audit', 5);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
        $this->assertSame(StatusBidaan::DalamPenyediaan, $this->bid->fresh()->status);
    }

    public function test_batch_stale_later_source_rolls_back_earlier_create_edit_and_audits(): void
    {
        $first = $this->draftSave((object) ['fixture' => 'keep first']);
        $last = $this->draftSave((object) ['fixture' => 'keep last']);
        $this->draftDenied(fn () => $this->draftBatchSave([
            $this->draftBatchRow('new'),
            $this->draftBatchRow('changed', $first),
            $this->draftBatchRow('stale', $last, ['semakan_butiran' => 0]),
        ]), 409);
        $this->assertSame('keep first', $first->fresh()->data_sumber->fixture);
        $this->assertSame('keep last', $last->fresh()->data_sumber->fixture);
        $this->assertSame(1, $first->fresh()->semakan);
        $this->assertSame(3, $this->bid->fresh()->semakan);
        $this->assertSame(3, $this->estimate->fresh()->semakan);
        $this->assertDatabaseCount('butiran_anggaran', 2);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    public function test_batch_original_create_retry_and_both_stale_parent_revisions_conflict(): void
    {
        $input = $this->draftBatchInput([$this->draftBatchRow('one'), $this->draftBatchRow('two')]);
        app(DrafButiranAnggaran::class)->simpanBanyak($this->estimate->id, $input);
        $this->draftDenied(fn () => app(DrafButiranAnggaran::class)->simpanBanyak($this->estimate->id, $input), 409);
        foreach (['semakan_bidaan', 'semakan_anggaran'] as $key) {
            $this->draftDenied(fn () => $this->draftBatchSave([$this->draftBatchRow('three')], [$key => 1]), 409);
        }
        $this->assertDatabaseCount('butiran_anggaran', 2);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    public function test_batch_validation_identifies_later_row_and_rolls_back_earlier_changes(): void
    {
        try {
            $this->draftBatchSave([$this->draftBatchRow('first'),
                $this->draftBatchRow('bad', replace: ['data_sumber' => (object) ['fixture' => 0.1]])]);
            $this->fail('Invalid source accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('kelompok.rekod.1.data_sumber', $exception->errors());
        }
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->assertSame(1, $this->bid->fresh()->semakan);
        $this->assertSame(1, $this->estimate->fresh()->semakan);
    }

    public function test_batch_rejects_ambiguous_duplicate_ids_unknown_metadata_and_non_list_rows(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'keep']);
        $row = $this->draftBatchRow('change', $source);
        foreach ([
            ['rekod' => [$row, array_replace($row, ['butiran_id' => (string) $source->id])]],
            ['rekod' => ['named' => $row]],
            ['rekod' => []],
            ['rekod' => [$row + ['ptj_id' => $this->owner->id]]],
            ['rekod' => [$row + ['reason' => 'Row override']]],
            ['rekod' => [$row + ['semakan_bidaan' => 999]]],
            ['ptj_id' => $this->owner->id],
        ] as $bad) {
            $this->draftDenied(fn () => $this->draftBatchSave([$row], $bad), 422);
        }
        $this->assertSame('keep', $source->fresh()->data_sumber->fixture);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_batch_own_workplace_scope_cannot_take_a_later_foreign_source_or_move_own_source(): void
    {
        $other = TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => 'Other batch sector', 'jenis' => 'sektor']);
        $foreign = $this->draftSave((object) ['fixture' => 'foreign'], replace: ['tempat_bertugas_id' => $other->id]);
        $own = $this->draftSave((object) ['fixture' => 'own']);
        $this->operator->penugasanPeranan()->delete();
        $this->draftRole($this->workplace->id);
        foreach ([
            $this->draftBatchRow('take', $foreign),
            $this->draftBatchRow('move', $own, ['tempat_bertugas_id' => $other->id]),
        ] as $denied) {
            $this->draftDenied(fn () => $this->draftBatchSave([$this->draftBatchRow('new'), $denied]), 403);
        }
        $this->assertSame($other->id, $foreign->fresh()->tempat_bertugas_id);
        $this->assertSame($this->workplace->id, $own->fresh()->tempat_bertugas_id);
        $this->assertSame(3, $this->bid->fresh()->semakan);
        $this->assertDatabaseCount('butiran_anggaran', 2);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    public function test_batch_wrong_parent_later_source_rolls_back_first_row(): void
    {
        $otherOs = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Batch sibling']);
        $otherEstimate = Anggaran::forceCreate(['bidaan_id' => $this->bid->id, 'ptj_id' => $this->owner->id,
            'rujukan_os_id' => $otherOs->id, 'diaktifkan' => true]);
        $foreign = ButiranAnggaran::forceCreate(['anggaran_id' => $otherEstimate->id, 'ptj_id' => $this->owner->id,
            'tempat_bertugas_id' => $this->workplace->id]);
        $this->draftDenied(fn () => $this->draftBatchSave([$this->draftBatchRow('new'),
            $this->draftBatchRow('foreign', $foreign)]), 404);
        $this->assertDatabaseCount('butiran_anggaran', 1);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->assertSame(1, $this->bid->fresh()->semakan);
    }

    public function test_batch_generated_output_guard_retains_every_original_row_and_exact_output(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'calculated']);
        $output = HasilPengiraan::forceCreate(['butiran_anggaran_id' => $source->id,
            'semakan_sumber' => $source->semakan, 'turutan' => 1, 'amaun' => '123.456789'])->fresh();
        $this->draftDenied(fn () => $this->draftBatchSave([$this->draftBatchRow('new'),
            $this->draftBatchRow('changed', $source)]), 409);
        $this->assertSame('123.456789', $output->fresh()->amaun);
        $this->assertSame('calculated', $source->fresh()->data_sumber->fixture);
        $this->assertDatabaseCount('butiran_anggaran', 1);
        $this->assertDatabaseCount('rekod_audit', 1);
        $this->assertSame(2, $this->estimate->fresh()->semakan);
    }

    public function test_batch_later_audit_insert_failure_restores_existing_source_and_all_parent_revisions(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'keep']);
        DB::unprepared(<<<'SQL'
CREATE FUNCTION batch_test_audit_failure() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.tindakan = 'source.draft_updated' THEN RAISE EXCEPTION 'Later batch audit unavailable'; END IF;
    RETURN NEW;
END;
$$;
CREATE TRIGGER batch_test_audit_failure BEFORE INSERT ON rekod_audit
FOR EACH ROW EXECUTE FUNCTION batch_test_audit_failure();
SQL);
        try {
            $this->draftBatchSave([$this->draftBatchRow('new'), $this->draftBatchRow('change', $source)]);
            $this->fail('Later audit failure ignored.');
        } catch (QueryException $exception) {
            $this->assertSame('P0001', (string) $exception->getCode());
        }
        $this->assertSame('keep', $source->fresh()->data_sumber->fixture);
        $this->assertSame(1, $source->fresh()->semakan);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertSame(2, $this->estimate->fresh()->semakan);
        $this->assertDatabaseCount('butiran_anggaran', 1);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_batch_supports_more_than_fifty_distinct_rows_without_merging_identical_values(): void
    {
        $rows = array_fill(0, 51, $this->draftBatchRow('same fixture'));
        $sources = $this->draftBatchSave($rows);
        $this->assertCount(51, array_unique(array_map(fn ($source) => $source->id, $sources)));
        $this->assertSame(52, $this->bid->fresh()->semakan);
        $this->assertDatabaseCount('butiran_anggaran', 51);
        $this->assertDatabaseCount('rekod_audit', 51);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
    }

    public function test_batch_rechecks_phase_central_role_session_and_workflow_before_even_noop_rows(): void
    {
        $source = $this->draftSave((object) ['fixture' => 'same']);
        $rows = [$this->draftBatchRow('same', $source)];
        $this->phase->forceFill(['dibuka' => false])->save();
        $this->draftDenied(fn () => $this->draftBatchSave($rows), 403);
        $this->phase->forceFill(['dibuka' => true])->save();
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => false]);
        $this->draftDenied(fn () => $this->draftBatchSave($rows), 403);
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => true]);
        $this->bid->forceFill(['status' => StatusBidaan::Muktamad])->save();
        $this->draftDenied(fn () => $this->draftBatchSave($rows), 409);
        $this->bid->forceFill(['status' => StatusBidaan::DalamPenyediaan])->save();
        $this->estimate->forceFill(['diaktifkan' => false])->save();
        $this->draftDenied(fn () => $this->draftBatchSave($rows), 409);
        $this->estimate->forceFill(['diaktifkan' => true])->save();
        $this->operator->penugasanPeranan()->delete();
        $this->draftDenied(fn () => $this->draftBatchSave($rows), 403);
        $this->draftRole();
        session()->put('abm_last_activity', now()->timestamp - 1800);
        $this->draftDenied(fn () => $this->draftBatchSave($rows), 403);
        $this->assertSame(2, $this->bid->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_batch_reasoned_superadmin_override_keeps_actor_and_per_row_audit_attribution(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::Superadmin]);
        $this->phase->forceFill(['dibuka' => false])->save();
        TetapanAksesPtj::query()->update(['dibenarkan' => false]);
        $rows = [$this->draftBatchRow('one'), $this->draftBatchRow('two')];
        $this->draftDenied(fn () => $this->draftBatchSave($rows), 422);
        $sources = $this->draftBatchSave($rows, ['reason' => 'Pembetulan pentadbiran berkelompok.']);
        foreach (RekodAudit::all() as $audit) {
            $this->assertSame($this->operator->id, $audit->pelaku_id);
            $this->assertSame(Peranan::Superadmin, $audit->peranan);
            $this->assertSame($this->workplace->id, $audit->tempat_bertugas_id);
            $this->assertSame('Pembetulan pentadbiran berkelompok.', $audit->alasan);
            $this->assertTrue($audit->selepas['pintasan']['fasa']);
            $this->assertNull($audit->versi_penghantaran);
        }
        $this->assertCount(2, $sources);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

}
