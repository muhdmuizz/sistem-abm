<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\ButiranAnggaran;
use App\Models\Fasa;
use App\Models\Kitaran;
use App\Models\PemetaanOs;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\RujukanOs;
use App\Models\TempatBertugas;
use App\Models\TetapanAksesPtj;
use App\Models\User;
use App\Services\PengaktifanKewangan;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PengaktifanKewanganTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Ptj $owner;
    private AktivitiKewangan $activity;
    private Kitaran $cycle;
    private Fasa $phase;
    private RujukanOs $os;
    private TempatBertugas $workplace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2028-05-15 12:00:00', 'Asia/Kuala_Lumpur'));
        $this->owner = Ptj::create(['kod_ptj' => 'ACT-001', 'nama' => 'Activation fixture', 'kategori' => 'PPD']);
        $this->activity = AktivitiKewangan::create(['nama' => 'Activation activity fixture']);
        $this->cycle = Kitaran::create(['tahun' => 2028]);
        $this->workplace = TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => 'Activation sector', 'jenis' => 'sektor']);
        $this->os = RujukanOs::create(['kod_objek' => '14000', 'butiran_perbelanjaan' => 'Activation OS fixture']);
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
            'tempat_bertugas_id' => $this->workplace->id, 'google_subject' => 'verified-activation-fixture'])->save();
        $this->activationAssign(Peranan::PenyediaBidaan);
        $this->activationSession();
    }

    private function activationSession(): void
    {
        $this->operator->refresh();
        $this->actingAs($this->operator)->withSession([
            'abm_access_revision' => $this->operator->access_revision,
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp,
        ]);
    }

    private function activationAssign(Peranan $role, ?int $ptj = null, ?int $activity = null, ?int $workplace = null): void
    {
        $preparer = in_array($role, [Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran], true);
        $scopedActivity = $preparer || in_array($role, [Peranan::KetuaBidaan, Peranan::PenyeliaBidaan], true);
        $this->operator->penugasanPeranan()->create([
            'peranan' => $role, 'ptj_id' => $preparer ? ($ptj ?? $this->owner->id) : null,
            'aktiviti_kewangan_id' => $scopedActivity ? ($activity ?? $this->activity->id) : null,
            'tempat_bertugas_id' => $workplace,
        ]);
    }

    private function activationBidFixture(bool $active = true, StatusBidaan $status = StatusBidaan::DalamPenyediaan): Bidaan
    {
        return Bidaan::forceCreate(['ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id,
            'kitaran_id' => $this->cycle->id, 'diaktifkan' => $active, 'semakan' => $active ? 1 : 0, 'status' => $status])->fresh();
    }

    private function activateFixtureBid(int $revision = 0, ?string $reason = null): Bidaan
    {
        return app(PengaktifanKewangan::class)->aktifkanBidaan($this->cycle->id, $this->owner->id, $this->activity->id, $revision, $reason);
    }

    private function activationDenied(callable $operation, int $status): void
    {
        try {
            $operation();
            $this->fail('Expected activation to be denied.');
        } catch (HttpException $exception) {
            $this->assertSame($status, $exception->getStatusCode());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->assertSame(404, $status);
        }
    }

    private function activationAuditFailure(): void
    {
        DB::unprepared(<<<'SQL'
CREATE FUNCTION activation_test_audit_failure() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN RAISE EXCEPTION 'Activation audit unavailable'; END;
$$;
CREATE TRIGGER activation_test_audit_failure BEFORE INSERT ON rekod_audit
FOR EACH ROW EXECUTE FUNCTION activation_test_audit_failure();
SQL);
    }

    public function test_bid_activation_persists_canonical_identity_and_actual_role_audit(): void
    {
        $bid = $this->activateFixtureBid();
        $this->assertTrue($bid->diaktifkan);
        $this->assertSame(StatusBidaan::DalamPenyediaan, $bid->status);
        $this->assertSame(1, $bid->semakan);
        $audit = RekodAudit::sole();
        $this->assertSame($this->operator->id, $audit->pelaku_id);
        $this->assertSame(Peranan::PenyediaBidaan, $audit->peranan);
        $this->assertSame($this->owner->id, $audit->ptj_id);
        $this->assertSame($this->activity->id, $audit->aktiviti_kewangan_id);
        $this->assertSame($this->cycle->id, $audit->kitaran_id);
        $this->assertSame('bid.activated', $audit->tindakan);
        $this->assertSame(['exists' => false], $audit->sebelum);
        $this->assertSame(false, $audit->selepas['pintasan']['kebenaran_pusat']);
        $this->assertSame(false, $audit->selepas['pintasan']['fasa']);
        $this->assertDatabaseCount('anggaran', 0);
    }

    public function test_current_retry_is_idempotent_and_stale_creation_cannot_duplicate_bid(): void
    {
        $bid = $this->activateFixtureBid();
        $again = $this->activateFixtureBid(1);
        $this->assertSame($bid->id, $again->id);
        $this->activationDenied(fn () => $this->activateFixtureBid(0), 409);
        $this->assertDatabaseCount('bidaan', 1);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_distinct_roles_do_not_acquire_each_others_activation_rights(): void
    {
        $bid = $this->activateFixtureBid();
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 403);
        $this->operator->penugasanPeranan()->delete();
        $this->activationAssign(Peranan::PenyediaAnggaran);
        $this->activationDenied(fn () => $this->activateFixtureBid(1), 403);
        $os = app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1);
        $this->assertTrue($os->diaktifkan);
        $this->assertSame(Peranan::PenyediaAnggaran, RekodAudit::orderByDesc('id')->first()->peranan);
    }

    public function test_multiple_roles_can_activate_both_entities_with_separate_audits(): void
    {
        $this->activationAssign(Peranan::PenyediaAnggaran);
        $bid = $this->activateFixtureBid();
        $os = app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1);
        $this->assertSame($bid->id, $os->bidaan_id);
        $this->assertSame($this->owner->id, $os->ptj_id);
        $this->assertSame(1, $os->semakan);
        $this->assertSame(2, $bid->fresh()->semakan);
        $audit = RekodAudit::orderByDesc('id')->first();
        $this->assertSame('estimate.activated', $audit->tindakan);
        $this->assertSame($os->id, $audit->selepas['anggaran']['id']);
        $this->assertDatabaseCount('rekod_audit', 2);
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
        $same = app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 2);
        $this->assertSame($os->id, $same->id);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    #[DataProvider('nonPreparerRoles')]
    public function test_reviewers_and_monitor_cannot_activate_financial_records(Peranan $role): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->activationAssign($role);
        $bid = $this->activationBidFixture();
        $this->activationDenied(fn () => $this->activateFixtureBid(1), 403);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 403);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public static function nonPreparerRoles(): array
    {
        return [[Peranan::KetuaBidaan], [Peranan::PenyeliaBidaan], [Peranan::PemantauBidaan]];
    }

    public function test_roles_are_exact_tuples_and_cannot_be_cross_combined(): void
    {
        $otherPtj = Ptj::create(['kod_ptj' => 'ACT-002', 'nama' => 'Other fixture', 'kategori' => 'PPD']);
        $otherActivity = AktivitiKewangan::create(['nama' => 'Other fixture']);
        $this->operator->penugasanPeranan()->delete();
        $this->activationAssign(Peranan::PenyediaBidaan, $this->owner->id, $otherActivity->id);
        $this->activationAssign(Peranan::PenyediaBidaan, $otherPtj->id, $this->activity->id);
        $this->activationDenied(fn () => $this->activateFixtureBid(), 403);
        $this->assertDatabaseCount('bidaan', 0);
    }

    public function test_central_parent_and_os_permissions_are_rechecked_after_prior_reads(): void
    {
        $bid = $this->activateFixtureBid();
        $this->activationAssign(Peranan::PenyediaAnggaran);
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => false]);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 403);
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => true]);
        TetapanAksesPtj::whereNull('rujukan_os_id')->update(['dibenarkan' => false]);
        $this->activationDenied(fn () => $this->activateFixtureBid(1), 403);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 403);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_unconfigured_os_is_not_implicitly_enabled_by_activity_permission(): void
    {
        $bid = $this->activateFixtureBid();
        $this->activationAssign(Peranan::PenyediaAnggaran);
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->delete();
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 403);
        $this->assertDatabaseCount('anggaran', 0);
    }

    public function test_closed_out_of_date_or_nonmatching_phase_does_not_allow_ordinary_activation(): void
    {
        $this->phase->forceFill(['dibuka' => false])->save();
        $this->activationDenied(fn () => $this->activateFixtureBid(), 403);
        $this->phase->forceFill(['dibuka' => true, 'tarikh_tamat' => '2028-05-14'])->save();
        $this->activationDenied(fn () => $this->activateFixtureBid(), 403);
        $this->phase->forceFill(['tarikh_tamat' => '2028-05-31'])->save();
        $this->phase->skop()->delete();
        $this->activationDenied(fn () => $this->activateFixtureBid(), 403);
        $this->assertDatabaseCount('bidaan', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_os_requires_active_bid_and_current_parent_revision(): void
    {
        $this->activationAssign(Peranan::PenyediaAnggaran);
        $bid = $this->activationBidFixture(false);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 0), 409);
        $bid = $this->activateFixtureBid();
        $os = app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 409);
        $this->assertSame(2, $bid->fresh()->semakan);
        $this->assertSame($os->id, $bid->anggaran()->sole()->id);
    }

    #[DataProvider('protectedStatuses')]
    public function test_initial_activation_never_mutates_submitted_returned_or_finalised_states(StatusBidaan $status): void
    {
        $this->activationAssign(Peranan::PenyediaAnggaran);
        $bid = $this->activationBidFixture(true, $status);
        $this->activationDenied(fn () => $this->activateFixtureBid(1), 409);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 409);
        $this->assertSame($status, $bid->fresh()->status);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->assertDatabaseCount('anggaran', 0);
    }

    public static function protectedStatuses(): array
    {
        return [[StatusBidaan::DalamSemakan], [StatusBidaan::Dikembalikan], [StatusBidaan::Muktamad]];
    }

    public function test_own_workplace_preparer_uses_registered_assignment_not_another_workplace(): void
    {
        $bid = $this->activationBidFixture();
        $this->operator->penugasanPeranan()->delete();
        $other = TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => 'Other sector', 'jenis' => 'sektor']);
        $this->activationAssign(Peranan::PenyediaAnggaran, workplace: $other->id);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 403);
        $this->activationAssign(Peranan::PenyediaAnggaran, workplace: $this->workplace->id);
        $os = app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1);
        $this->assertTrue($os->diaktifkan);
        $this->assertDatabaseCount('butiran_anggaran', 0);
    }

    public function test_superadmin_override_requires_reason_and_preserves_actual_actor_and_scope(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->activationAssign(Peranan::Superadmin);
        $this->phase->forceFill(['dibuka' => false])->save();
        TetapanAksesPtj::query()->update(['dibenarkan' => false]);
        try {
            $this->activateFixtureBid();
            $this->fail('Unreasoned override allowed.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
        }
        $bid = $this->activateFixtureBid(0, 'Ujian pentadbiran yang dibenarkan.');
        $os = app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1, 'Ujian pentadbiran OS.');
        foreach (RekodAudit::all() as $audit) {
            $this->assertSame($this->operator->id, $audit->pelaku_id);
            $this->assertSame(Peranan::Superadmin, $audit->peranan);
            $this->assertSame(true, $audit->selepas['pintasan']['kebenaran_pusat']);
            $this->assertSame(true, $audit->selepas['pintasan']['fasa']);
            $this->assertNotEmpty($audit->alasan);
        }
        $this->assertTrue($os->diaktifkan);
    }

    public function test_even_superadmin_cannot_activate_excluded_or_unmapped_catalogue_os(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->activationAssign(Peranan::Superadmin);
        $bid = $this->activationBidFixture();
        $this->os->update(['dikecualikan' => true]);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1, 'Semak katalog.'), 404);
        $this->os->update(['dikecualikan' => false]);
        PemetaanOs::query()->delete();
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1, 'Semak pemetaan.'), 404);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    #[DataProvider('invalidSessions')]
    public function test_service_denies_expired_revoked_or_invalid_authenticated_sessions(string $fault): void
    {
        match ($fault) {
            'idle' => session()->put('abm_last_activity', now()->timestamp - 1800),
            'absolute' => session()->put('abm_authenticated_at', now()->timestamp - 28800),
            'missing' => session()->forget('abm_authenticated_at'),
            'future' => session()->put('abm_last_activity', now()->timestamp + 1),
            'revoked' => $this->operator->increment('access_revision'),
            'inactive' => $this->operator->forceFill(['is_active' => false])->save(),
            'subject' => $this->operator->forceFill(['google_subject' => null])->save(),
            'roles' => $this->operator->penugasanPeranan()->delete(),
        };
        $this->activationDenied(fn () => $this->activateFixtureBid(), 403);
        $this->assertDatabaseCount('bidaan', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public static function invalidSessions(): array
    {
        return array_map(fn ($fault) => [$fault], ['idle', 'absolute', 'missing', 'future', 'revoked', 'inactive', 'subject', 'roles']);
    }

    public function test_database_audit_failure_rolls_back_new_bid_activation(): void
    {
        $this->activationAuditFailure();
        try {
            $this->activateFixtureBid();
            $this->fail('Audit failure ignored.');
        } catch (QueryException $exception) {
            $this->assertSame('P0001', (string) $exception->getCode());
        }
        $this->assertDatabaseCount('bidaan', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_database_audit_failure_rolls_back_os_and_parent_revision(): void
    {
        $this->activationAssign(Peranan::PenyediaAnggaran);
        $bid = $this->activateFixtureBid();
        $this->activationAuditFailure();
        try {
            app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1);
            $this->fail('Audit failure ignored.');
        } catch (QueryException $exception) {
            $this->assertSame('P0001', (string) $exception->getCode());
        }
        $this->assertSame(1, $bid->fresh()->semakan);
        $this->assertTrue($bid->fresh()->diaktifkan);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_reactivation_of_retained_source_data_is_deferred_without_changing_any_identity(): void
    {
        $this->activationAssign(Peranan::PenyediaAnggaran);
        $bid = $this->activationBidFixture();
        $os = Anggaran::forceCreate(['bidaan_id' => $bid->id, 'ptj_id' => $this->owner->id, 'rujukan_os_id' => $this->os->id]);
        $source = ButiranAnggaran::forceCreate(['anggaran_id' => $os->id, 'ptj_id' => $this->owner->id,
            'tempat_bertugas_id' => $this->workplace->id]);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 409);
        $this->assertFalse($os->fresh()->diaktifkan);
        $this->assertTrue($source->fresh()->anggaran->is($os));
        $this->assertSame(1, $bid->fresh()->semakan);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_superadmin_must_obey_protected_state_and_stale_revision_even_with_an_override_reason(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->activationAssign(Peranan::Superadmin);
        $bid = $this->activationBidFixture(true, StatusBidaan::Muktamad);
        $this->activationDenied(fn () => $this->activateFixtureBid(1, 'Pentadbiran.'), 409);
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1, 'Pentadbiran.'), 409);
        $bid->forceFill(['status' => StatusBidaan::DalamPenyediaan])->save();
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 0, 'Pentadbiran.'), 409);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_closed_phase_is_rechecked_before_os_creation_after_bid_activation(): void
    {
        $this->activationAssign(Peranan::PenyediaAnggaran);
        $bid = $this->activateFixtureBid();
        $this->phase->forceFill(['dibuka' => false])->save();
        $this->activationDenied(fn () => app(PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id, 1), 403);
        $this->assertSame(1, $bid->fresh()->semakan);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_inactive_bid_with_existing_os_is_not_silently_repaired_by_activation(): void
    {
        $bid = $this->activationBidFixture(false);
        Anggaran::forceCreate(['bidaan_id' => $bid->id, 'ptj_id' => $this->owner->id, 'rujukan_os_id' => $this->os->id]);
        $this->activationDenied(fn () => $this->activateFixtureBid(), 409);
        $this->assertFalse($bid->fresh()->diaktifkan);
        $this->assertDatabaseCount('anggaran', 1);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_database_accepts_only_the_four_approved_workflow_statuses(): void
    {
        $bid = $this->activationBidFixture();
        $this->expectException(QueryException::class);
        DB::table('bidaan')->where('id', $bid->id)->update(['status' => 'diluluskan']);
    }
}
