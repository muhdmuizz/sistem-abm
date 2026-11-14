<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\Fasa;
use App\Models\Kitaran;
use App\Models\PemetaanOs;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\RujukanOs;
use App\Models\TetapanAksesPtj;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BidaPeruntukanWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private Ptj $owner;
    private Ptj $other;
    private AktivitiKewangan $activity;
    private Kitaran $cycle;
    private Fasa $phase;
    private RujukanOs $os;
    private RujukanOs $unconfigured;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2028-05-15 12:00:00', 'Asia/Kuala_Lumpur'));
        $this->owner = Ptj::create(['kod_ptj' => 'WORK-001', 'nama' => 'JPNT', 'kategori' => 'JPNT']);
        $this->other = Ptj::create(['kod_ptj' => 'WORK-002', 'nama' => 'PPD Kuala Nerus', 'kategori' => 'PPD']);
        $this->activity = AktivitiKewangan::create(['nama' => 'Pengurusan Pentadbiran Negeri', 'singkatan' => 'PPN']);
        $this->cycle = Kitaran::create(['tahun' => 2028]);
        $this->phase = Fasa::forceCreate(['kitaran_id' => $this->cycle->id, 'tahun_kitaran' => 2028,
            'jenis' => 'bidaan_awal', 'tarikh_mula' => '2028-05-01', 'tarikh_tamat' => '2028-05-31',
            'jenis_skop' => 'global', 'dibuka' => true, 'pernah_dibuka' => true]);
        $this->phase->skop()->create(['jenis_skop' => 'global']);
        $this->os = RujukanOs::create(['kod_objek' => '14000', 'butiran_perbelanjaan' => 'Bayaran Lebih Masa']);
        $this->unconfigured = RujukanOs::create(['kod_objek' => '15000', 'butiran_perbelanjaan' => 'Faedah Kewangan Lain']);
        foreach ([$this->os, $this->unconfigured] as $os) {
            PemetaanOs::create(['aktiviti_kewangan_id' => $this->activity->id, 'rujukan_os_id' => $os->id,
                'singkatan_sumber' => 'PPN', 'butiran_asal' => $os->butiran_perbelanjaan, 'status_sumber' => 'Aktif']);
        }
        foreach ([null, $this->os->id] as $osId) {
            TetapanAksesPtj::create(['kategori_ptj' => 'JPNT', 'aktiviti_kewangan_id' => $this->activity->id,
                'rujukan_os_id' => $osId, 'dibenarkan' => true]);
        }
        $this->operator = User::factory()->create(['name' => 'Muhammad Muizzuddin bin Zaini']);
        $this->operator->forceFill(['is_active' => true, 'ptj_id' => $this->owner->id, 'google_subject' => 'workspace-fixture'])->save();
        $this->workspaceRole(Peranan::PenyediaBidaan);
        $this->workspaceRole(Peranan::PenyediaAnggaran);
        $this->actingAs($this->operator)->withSession(['abm_access_revision' => $this->operator->fresh()->access_revision,
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp]);
    }

    private function workspaceRole(Peranan $role): void
    {
        $scope = ['peranan' => $role];
        if (in_array($role, [Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran], true)) {
            $scope += ['ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id];
        } elseif (in_array($role, [Peranan::KetuaBidaan, Peranan::PenyeliaBidaan], true)) {
            $scope['aktiviti_kewangan_id'] = $this->activity->id;
        } elseif ($role === Peranan::PemantauBidaan) {
            $scope['ptj_id'] = $this->owner->id;
        }
        $this->operator->penugasanPeranan()->create($scope);
    }

    private function workspaceIndex(): string
    {
        return route('bids.index', ['kitaran_id' => $this->cycle->id, 'ptj_id' => $this->owner->id]);
    }

    private function workspaceActivateUrl(): string
    {
        return route('bids.activate', [$this->cycle->id, $this->owner->id, $this->activity->id]);
    }

    private function workspaceBid(): Bidaan
    {
        return Bidaan::forceCreate(['kitaran_id' => $this->cycle->id, 'ptj_id' => $this->owner->id,
            'aktiviti_kewangan_id' => $this->activity->id, 'diaktifkan' => true, 'semakan' => 1])->fresh();
    }

    private function workspaceCapture(string $name, $response): void
    {
        if (getenv('ABM_UI_CAPTURE') !== '1') { return; }
        $directory = storage_path('app/ui-qa');
        if (! is_dir($directory)) { mkdir($directory, 0775, true); }
        // Static browser fixtures run on the real clock, not this test's future Kitaran clock.
        $html = preg_replace('/data-deadline="\d+"/', 'data-deadline="'.(time() + 3600).'"', $response->getContent());
        file_put_contents($directory.'/'.$name.'.html',
            str_replace('http://localhost', 'http://127.0.0.1:4173', $html));
        foreach (['abm.css', 'abm.js'] as $asset) { copy(public_path($asset), $directory.'/'.$asset); }
    }

    public function test_initial_context_and_available_cards_never_create_financial_records_on_get(): void
    {
        $initial = $this->get('/bida-peruntukan')->assertOk()->assertSee('Pilih Kitaran ABM dan PTJ.')
            ->assertSee('Bida Peruntukan')->assertDontSee('PPD Kuala Nerus');
        $cards = $this->get($this->workspaceIndex())->assertOk()->assertSee('Belum Aktif')->assertSee('Aktifkan Bidaan')
            ->assertSee('Pengurusan Pentadbiran Negeri');
        $this->get($this->workspaceActivateUrl())->assertOk()->assertSee('name="semakan" value="0"', false);
        $this->assertDatabaseCount('bidaan', 0);
        $this->assertDatabaseCount('anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->workspaceCapture('bid-context', $initial);
        $this->workspaceCapture('bid-list', $cards);
    }

    public function test_no_cycle_state_is_padded_and_does_not_assume_a_year(): void
    {
        $this->phase->skop()->delete();
        $this->phase->delete();
        $this->cycle->delete();
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::Superadmin);
        $response = $this->get('/bida-peruntukan')->assertOk()->assertSee('Belum ada Kitaran ABM.')
            ->assertSee('class="empty-state"', false)->assertSee('Tambah Kitaran')->assertDontSee('2028');
        $this->workspaceCapture('bid-empty', $response);
    }

    public function test_post_activation_calls_real_service_and_redirects_to_saved_bid(): void
    {
        $this->from($this->workspaceActivateUrl())->post($this->workspaceActivateUrl(), ['semakan' => 0])->assertRedirect();
        $bid = Bidaan::sole();
        $this->assertTrue($bid->diaktifkan);
        $this->assertSame('bid.activated', RekodAudit::sole()->tindakan);
        $this->get(route('bids.show', $bid->id))->assertOk()->assertSee('Dalam Penyediaan')->assertSee('OS tersedia');
    }

    public function test_os_catalogue_is_exactly_scoped_and_post_activation_persists_and_audits(): void
    {
        $bid = $this->workspaceBid();
        $response = $this->get(route('bids.show', $bid->id))->assertOk()->assertSee('OS14000')
            ->assertDontSee('OS15000')->assertDontSee('RM 0')->assertDontSee('Lengkap');
        $path = route('bids.os-activate', [$bid->id, $this->os->id]);
        $this->get($path)->assertOk()->assertSee('Aktifkan OS');
        $this->post($path, ['semakan' => 1])->assertRedirect(route('bids.show', $bid->id));
        $estimate = Anggaran::sole();
        $this->assertTrue($estimate->diaktifkan);
        $this->assertSame($this->os->id, $estimate->rujukan_os_id);
        $this->assertSame('estimate.activated', RekodAudit::sole()->tindakan);
        $this->get(route('bids.show', $bid->id))->assertOk()->assertSee('Penyediaan butiran OS ini belum dibuka.');
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->workspaceCapture('bid-os', $response);
    }

    public function test_cross_scope_context_and_direct_record_ids_are_denied(): void
    {
        $this->get(route('bids.index', ['kitaran_id' => $this->cycle->id, 'ptj_id' => $this->other->id]))->assertForbidden();
        $foreign = Bidaan::forceCreate(['kitaran_id' => $this->cycle->id, 'ptj_id' => $this->other->id,
            'aktiviti_kewangan_id' => $this->activity->id, 'diaktifkan' => true]);
        $this->get(route('bids.show', $foreign->id))->assertForbidden();
        $this->post(route('bids.os-activate', [$foreign->id, $this->os->id]), ['semakan' => 0])->assertForbidden();
        $this->assertDatabaseCount('anggaran', 0);
    }

    public function test_read_only_monitor_can_view_but_has_no_activation_controls_or_post_authority(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PemantauBidaan);
        $this->get($this->workspaceIndex())->assertOk()->assertDontSee('>Aktifkan Bidaan<', false);
        $this->get($this->workspaceActivateUrl())->assertForbidden();
        $this->post($this->workspaceActivateUrl(), ['semakan' => 0])->assertForbidden();
        $bid = $this->workspaceBid();
        $this->get(route('bids.show', $bid->id))->assertOk()->assertDontSee('>Aktifkan OS<', false);
        $this->post(route('bids.os-activate', [$bid->id, $this->os->id]), ['semakan' => 1])->assertForbidden();
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_reviewer_only_role_does_not_grant_preparation_workspace_access(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PenyeliaBidaan);
        $this->get('/bida-peruntukan')->assertForbidden();
        $this->get('/utama')->assertOk()->assertDontSee('aria-label="Bida Peruntukan"', false);
    }

    public function test_activation_roles_are_distinct_in_visible_controls_and_direct_posts(): void
    {
        $this->operator->penugasanPeranan()->where('peranan', Peranan::PenyediaBidaan)->delete();
        $this->get($this->workspaceIndex())->assertOk()->assertSee('Pengaktifan oleh Penyedia Bidaan.');
        $this->post($this->workspaceActivateUrl(), ['semakan' => 0])->assertForbidden();
        $bid = $this->workspaceBid();
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PenyediaBidaan);
        $this->get(route('bids.show', $bid->id))->assertOk()->assertSee('Pengaktifan oleh Penyedia Anggaran.');
        $this->post(route('bids.os-activate', [$bid->id, $this->os->id]), ['semakan' => 1])->assertForbidden();
    }

    public function test_closed_phase_is_read_only_for_preparers_but_not_an_implicit_loss_of_view_access(): void
    {
        $this->phase->forceFill(['dibuka' => false])->save();
        $this->get($this->workspaceIndex())->assertOk()->assertSee('Fasa belum dibuka');
        $this->post($this->workspaceActivateUrl(), ['semakan' => 0])->assertForbidden();
        $this->assertDatabaseCount('bidaan', 0);
    }

    public function test_central_revocation_is_rechecked_on_a_new_request_and_forged_payload_cannot_expand_scope(): void
    {
        $this->get($this->workspaceIndex())->assertOk()->assertSee('Aktifkan Bidaan');
        TetapanAksesPtj::whereNull('rujukan_os_id')->update(['dibenarkan' => false]);
        $response = $this->get($this->workspaceIndex())->assertOk()->assertSee('Tiada Aktiviti Kewangan dibenarkan.');
        $this->post($this->workspaceActivateUrl(), ['semakan' => 0, 'ptj_id' => $this->other->id,
            'peranan' => 'superadmin'])->assertForbidden();
        $this->assertDatabaseCount('bidaan', 0);
        $this->workspaceCapture('bid-no-results', $response);
    }

    public function test_superadmin_override_form_validates_reason_and_preserves_it_on_stale_failure(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::Superadmin);
        $this->phase->forceFill(['dibuka' => false])->save();
        TetapanAksesPtj::query()->update(['dibenarkan' => false]);
        $form = $this->get($this->workspaceActivateUrl())->assertOk()->assertSee('Alasan pintasan')
            ->assertSee('required', false)->assertSee('pintasan Superadmin');
        $this->from($this->workspaceActivateUrl())->post($this->workspaceActivateUrl(),
            ['semakan' => 0, 'activation_context' => 'bid/'.$this->cycle->id.'/'.$this->owner->id.'/'.$this->activity->id])
            ->assertSessionHasErrors('reason');
        $this->post($this->workspaceActivateUrl(), ['semakan' => 0, 'reason' => 'Pembukaan pentadbiran yang dibenarkan.'])->assertRedirect();
        $bid = Bidaan::sole();
        $this->assertSame(Peranan::Superadmin, RekodAudit::sole()->peranan);
        $this->assertTrue(RekodAudit::sole()->selepas['pintasan']['fasa']);
        $path = route('bids.os-activate', [$bid->id, $this->unconfigured->id]);
        $this->get($path)->assertOk()->assertSee('Alasan pintasan');
        $this->from($path)->post($path, ['semakan' => 0, 'reason' => 'Sebab dikekalkan.',
            'activation_context' => 'os/'.$bid->id.'/'.$this->unconfigured->id])->assertSessionHasErrors('save')
            ->assertSessionHasInput('reason', 'Sebab dikekalkan.');
        $this->assertDatabaseCount('anggaran', 0);
        $this->workspaceCapture('bid-activation', $form);
    }

    public function test_override_validation_state_renders_accessible_field_errors(): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::Superadmin);
        $this->phase->forceFill(['dibuka' => false])->save();
        TetapanAksesPtj::query()->update(['dibenarkan' => false]);
        // Fresh GET fixture: POST reason rejection is verified separately above.
        // Laravel 13 JSON sessions marshal this serialized structure on request start.
        $errors = ['default' => ['format' => ':message', 'messages' => ['reason' => ['Alasan pintasan diperlukan.']]]];
        $response = $this->withSession(['errors' => $errors, '_old_input' => [
            'activation_context' => 'bid/'.$this->cycle->id.'/'.$this->owner->id.'/'.$this->activity->id,
            'reason' => '',
        ]])->get($this->workspaceActivateUrl())->assertOk()->assertSee('aria-invalid="true"', false)
            ->assertSee('Alasan pintasan diperlukan.')->assertSee('activation-reason-error');
        $this->workspaceCapture('bid-validation', $response);
    }

    public function test_database_audit_failure_gives_truthful_error_and_no_partial_activation(): void
    {
        DB::unprepared(<<<'SQL'
CREATE FUNCTION workspace_test_audit_failure() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN RAISE EXCEPTION 'Workspace audit unavailable'; END;
$$;
CREATE TRIGGER workspace_test_audit_failure BEFORE INSERT ON rekod_audit
FOR EACH ROW EXECUTE FUNCTION workspace_test_audit_failure();
SQL);
        $this->from($this->workspaceActivateUrl())->post($this->workspaceActivateUrl(), ['semakan' => 0])
            ->assertSessionHasErrors('save')->assertSessionMissing('status');
        $this->assertDatabaseCount('bidaan', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_unconfigured_excluded_unmapped_os_and_nonpreparation_states_have_no_activation_route(): void
    {
        $bid = $this->workspaceBid();
        $path = route('bids.os-activate', [$bid->id, $this->unconfigured->id]);
        $this->get($path)->assertNotFound();
        $this->post($path, ['semakan' => 1])->assertNotFound();
        $this->os->update(['dikecualikan' => true]);
        $this->get(route('bids.os-form', [$bid->id, $this->os->id]))->assertNotFound();
        $this->os->update(['dikecualikan' => false]);
        $bid->forceFill(['status' => StatusBidaan::DalamSemakan])->save();
        $this->get(route('bids.show', $bid->id))->assertOk()->assertSee('Dalam Semakan')
            ->assertDontSee('>Aktifkan OS<', false);
        $this->post(route('bids.os-activate', [$bid->id, $this->os->id]), ['semakan' => 1])->assertSessionHasErrors('save');
        $this->assertDatabaseCount('anggaran', 0);
    }

    public function test_empty_os_catalogue_has_a_padded_meaningful_state_without_fabricated_amounts(): void
    {
        $bid = $this->workspaceBid();
        TetapanAksesPtj::whereNotNull('rujukan_os_id')->update(['dibenarkan' => false]);
        $response = $this->get(route('bids.show', $bid->id))->assertOk()->assertSee('Tiada OS tersedia dalam skop anda.')
            ->assertSee('class="empty-state"', false)->assertDontSee('RM 0');
        $this->workspaceCapture('bid-os-empty', $response);
    }

    public function test_authentication_expiry_and_request_validation_guard_the_new_routes(): void
    {
        $this->get(route('bids.index', ['kitaran_id' => 'bad']))->assertSessionHasErrors('kitaran_id');
        $this->get(route('bids.index', ['kitaran_id' => 999999]))->assertNotFound();
        $this->post($this->workspaceActivateUrl(), [])->assertSessionHasErrors('semakan');
        session()->put('abm_last_activity', now()->timestamp - 1800);
        $this->post($this->workspaceActivateUrl(), ['semakan' => 0])->assertRedirect('/login');
        $this->assertDatabaseCount('bidaan', 0);
    }
    private function workspaceEstimate(Bidaan $bid, ?RujukanOs $os = null, bool $active = true): Anggaran
    {
        return Anggaran::forceCreate(['bidaan_id' => $bid->id, 'ptj_id' => $bid->ptj_id,
            'rujukan_os_id' => ($os ?? $this->os)->id, 'diaktifkan' => $active])->fresh();
    }

    private function workspacePlace(string $name): \App\Models\TempatBertugas
    {
        return \App\Models\TempatBertugas::create(['ptj_id' => $this->owner->id, 'nama' => $name, 'jenis' => 'sektor']);
    }

    private function workspaceSource(Anggaran $estimate, \App\Models\TempatBertugas $place): \App\Models\ButiranAnggaran
    {
        return \App\Models\ButiranAnggaran::forceCreate(['anggaran_id' => $estimate->id, 'ptj_id' => $this->owner->id,
            'tempat_bertugas_id' => $place->id, 'data_sumber' => (object) ['private_fixture' => 'SECRET_SOURCE_VALUE']]);
    }

    public function test_active_os_opens_actual_estimate_context_without_creating_or_fabricating_financial_data(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $this->get(route('bids.show', $bid->id))->assertOk()->assertSee($url, false);
        $response = $this->get($url)->assertOk()->assertSee('Senarai Butiran Anggaran')
            ->assertSee('Kembali ke Senarai Anggaran')->assertSee('2028')->assertSee('Pengurusan Pentadbiran Negeri')
            ->assertSee('Tiada Butiran Anggaran dalam skop anda.')->assertSee('Penyediaan butiran OS ini belum dibuka.')
            ->assertDontSee('RM 0')->assertDontSee('Tandakan Lengkap')->assertDontSee('Tambah Butiran');
        $this->assertDatabaseCount('bidaan', 1);
        $this->assertDatabaseCount('anggaran', 1);
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->workspaceCapture('estimate-empty', $response);
    }

    public function test_os_workspace_selects_only_saved_metadata_and_presents_workplaces_with_no_source_json(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $first = $this->workspaceSource($estimate, $this->workspacePlace('Sektor Pengurusan'));
        $this->workspaceSource($estimate, $this->workspacePlace('Sektor Perancangan dan Pengurusan Pendidikan'));
        $response = $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
            ->assertSee('Sektor Pengurusan')->assertSee('Sektor Perancangan dan Pengurusan Pendidikan')
            ->assertSee('Butiran #'.$first->id)->assertSee('2 rekod dalam skop anda')
            ->assertDontSee('SECRET_SOURCE_VALUE')->assertDontSee('private_fixture')->assertDontSeeText('RM ');
        $row = app(\App\Services\RuangAnggaran::class)->sources($this->operator, $bid, $estimate)->first();
        $this->assertArrayNotHasKey('data_sumber', $row->getAttributes());
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->workspaceCapture('estimate-populated', $response);
    }

    public function test_workplace_only_rows_and_pagination_counts_are_scoped_before_querying(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $own = $this->workspacePlace('Sektor Sendiri');
        $other = $this->workspacePlace('SECTOR_NOT_AUTHORISED');
        for ($i = 0; $i < 26; $i++) { $this->workspaceSource($estimate, $own); }
        for ($i = 0; $i < 10; $i++) { $this->workspaceSource($estimate, $other); }
        $this->operator->penugasanPeranan()->delete();
        $this->operator->forceFill(['tempat_bertugas_id' => $own->id])->save();
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::PenyediaAnggaran,
            'ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id, 'tempat_bertugas_id' => $own->id]);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $response = $this->get($url)->assertOk()->assertSee('26 rekod dalam skop anda')
            ->assertSee('Halaman 1 daripada 2')->assertDontSee('SECTOR_NOT_AUTHORISED')->assertDontSee('SECRET_SOURCE_VALUE');
        $this->assertCount(25, $response->viewData('sources')->items());
        $second = $this->get($url.'?page=2')->assertOk()->assertSee('Halaman 2 daripada 2')
            ->assertDontSee('SECTOR_NOT_AUTHORISED');
        $this->assertCount(1, $second->viewData('sources')->items());
        $this->get($url.'?page=999')->assertRedirect(route('estimates.show', [$bid->id, $estimate->id, 'page' => 2]));
        $this->get($url.'?tempat_bertugas_id='.$other->id)->assertOk()->assertSee('26 rekod dalam skop anda')->assertDontSee('SECTOR_NOT_AUTHORISED');
        $this->workspaceCapture('estimate-workplace', $response);
    }

    public function test_empty_own_scope_does_not_reveal_that_foreign_workplace_rows_exist(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $own = $this->workspacePlace('Own empty workplace');
        $this->workspaceSource($estimate, $this->workspacePlace('Hidden populated workplace'));
        $this->operator->penugasanPeranan()->delete();
        $this->operator->forceFill(['tempat_bertugas_id' => $own->id])->save();
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::PenyediaAnggaran,
            'ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id, 'tempat_bertugas_id' => $own->id]);
        $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
            ->assertSee('0 rekod dalam skop anda')->assertDontSee('Hidden populated workplace');
    }

    public function test_workspace_role_tuples_cannot_cross_combine_and_wrong_registered_workplace_is_denied(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $own = $this->workspacePlace('Own assigned workplace');
        $other = $this->workspacePlace('Different registered workplace');
        $this->workspaceSource($estimate, $own);
        $this->operator->penugasanPeranan()->delete();
        $this->operator->forceFill(['tempat_bertugas_id' => $other->id])->save();
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::PenyediaAnggaran,
            'ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id, 'tempat_bertugas_id' => $own->id]);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $this->get($url)->assertForbidden();
        $this->operator->penugasanPeranan()->delete();
        $anotherActivity = AktivitiKewangan::create(['nama' => 'Unrelated activity']);
        foreach ([[$this->owner->id, $anotherActivity->id], [$this->other->id, $this->activity->id]] as [$ptjId, $activityId]) {
            $this->operator->penugasanPeranan()->create(['peranan' => Peranan::PenyediaBidaan,
                'ptj_id' => $ptjId, 'aktiviti_kewangan_id' => $activityId]);
        }
        $this->get($url)->assertForbidden();
    }

    public function test_read_only_monitor_and_bid_preparer_can_view_scoped_metadata_without_write_routes(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $this->workspaceSource($estimate, $this->workspacePlace('Sektor Paparan'));
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        foreach ([Peranan::PemantauBidaan, Peranan::PenyediaBidaan] as $role) {
            $this->operator->penugasanPeranan()->delete();
            $this->workspaceRole($role);
            $response = $this->get($url)->assertOk()->assertSee('Sektor Paparan')
                ->assertDontSee('Tambah Butiran')->assertDontSee('name="data_sumber"', false);
            $this->post($url, ['data_sumber' => 'forged'])->assertStatus(405);
            if ($role === Peranan::PemantauBidaan) { $this->workspaceCapture('estimate-readonly', $response); }
        }
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_closed_phase_and_protected_workflow_states_preserve_authorised_read_only_access(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $this->phase->forceFill(['dibuka' => false])->save();
        foreach ([StatusBidaan::DalamSemakan, StatusBidaan::Muktamad] as $status) {
            $bid->forceFill(['status' => $status])->save();
            $response = $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
                ->assertSee('Tiada Fasa dibuka dalam tempoh.')->assertDontSee('Tambah Butiran');
        }
        $this->workspaceCapture('estimate-closed', $response);
    }

    public function test_nested_bid_estimate_ids_inactive_and_excluded_os_are_checked(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $secondBid = $this->workspaceBidForOtherCycle();
        $foreign = $this->workspaceEstimate($secondBid);
        $this->get(route('estimates.show', [$bid->id, $foreign->id]))->assertNotFound();
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $estimate->forceFill(['diaktifkan' => false])->save();
        $this->get($url)->assertConflict();
        $estimate->forceFill(['diaktifkan' => true])->save();
        $this->os->forceFill(['dikecualikan' => true])->save();
        $this->get($url)->assertNotFound();
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    private function workspaceBidForOtherCycle(): Bidaan
    {
        $cycle = Kitaran::create(['tahun' => 2029]);
        return Bidaan::forceCreate(['kitaran_id' => $cycle->id, 'ptj_id' => $this->owner->id,
            'aktiviti_kewangan_id' => $this->activity->id, 'diaktifkan' => true])->fresh();
    }

    public function test_navigation_keeps_bid_identity_and_inactive_os_requires_explicit_activation(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        TetapanAksesPtj::create(['kategori_ptj' => 'JPNT', 'aktiviti_kewangan_id' => $this->activity->id,
            'rujukan_os_id' => $this->unconfigured->id, 'dibenarkan' => true]);
        $nav = route('estimates.navigate', $bid->id);
        $this->get($nav.'?os_id='.$this->os->id)->assertRedirect(route('estimates.show', [$bid->id, $estimate->id]));
        $this->get($nav.'?os_id='.$this->unconfigured->id)->assertRedirect(route('bids.os-form', [$bid->id, $this->unconfigured->id]));
        $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
            ->assertSee('Faedah Kewangan Lain (Belum Aktif)')->assertSee('rel="next"', false);
        $this->assertDatabaseCount('anggaran', 1);
        $second = $this->workspaceEstimate($bid, $this->unconfigured);
        $this->get($nav.'?os_id='.$this->unconfigured->id)->assertRedirect(route('estimates.show', [$bid->id, $second->id]));
        $this->get(route('estimates.show', [$bid->id, $second->id]))->assertSee('rel="prev"', false)->assertDontSee('rel="next"', false);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_navigation_monitor_inactive_target_returns_to_catalogue_and_reviewer_is_denied(): void
    {
        $bid = $this->workspaceBid();
        TetapanAksesPtj::create(['kategori_ptj' => 'JPNT', 'aktiviti_kewangan_id' => $this->activity->id,
            'rujukan_os_id' => $this->unconfigured->id, 'dibenarkan' => true]);
        $estimate = $this->workspaceEstimate($bid);
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PemantauBidaan);
        $this->get(route('estimates.navigate', $bid->id).'?os_id='.$this->unconfigured->id)
            ->assertRedirect(route('bids.show', $bid->id).'#os-'.$this->unconfigured->id);
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PenyeliaBidaan);
        $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertForbidden();
        $this->assertDatabaseCount('anggaran', 1);
    }

    public function test_navigation_and_workspace_recheck_central_grants_session_and_input(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $nav = route('estimates.navigate', $bid->id);
        $this->get($url)->assertOk();
        $this->from($url)->get($nav)->assertSessionHasErrors('os_id');
        $this->get($nav.'?os_id=999999')->assertNotFound();
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => false]);
        $this->get($url)->assertNotFound();
        $this->get($nav.'?os_id='.$this->os->id)->assertNotFound();
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => true]);
        session()->put('abm_last_activity', now()->timestamp - 1800);
        $this->get($url)->assertRedirect('/login');
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_workspace_validation_summary_is_accessible_and_does_not_expose_raw_inputs(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $errors = ['default' => ['format' => ':message', 'messages' => ['os_id' => ['Pilih OS yang sah.']]]];
        $response = $this->withSession(['errors' => $errors])->get(route('estimates.show', [$bid->id, $estimate->id]))
            ->assertOk()->assertSee('role="alert"', false)->assertSee('Pilih OS yang sah.');
        $this->workspaceCapture('estimate-validation', $response);
    }

    public function test_cross_ptj_estimate_is_denied_and_superadmin_reads_real_context_without_mutation(): void
    {
        $bid = Bidaan::forceCreate(['kitaran_id' => $this->cycle->id, 'ptj_id' => $this->other->id,
            'aktiviti_kewangan_id' => $this->activity->id, 'diaktifkan' => true])->fresh();
        $estimate = $this->workspaceEstimate($bid);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $this->get($url)->assertForbidden();
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::Superadmin);
        $this->get($url)->assertOk()->assertSee('PPD Kuala Nerus')->assertSee('Pengurusan Pentadbiran Negeri');
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->assertDatabaseCount('anggaran', 1);
    }

    private function historyActivate(Bidaan $bid): Anggaran
    {
        return app(\App\Services\PengaktifanKewangan::class)->aktifkanOs($bid->id, $this->os->id,
            $bid->fresh()->semakan, 'INTERNAL_REASON_NOT_FOR_DISPLAY');
    }

    private function historySave(Bidaan $bid, Anggaran $estimate, \App\Models\TempatBertugas $place,
        string $value, ?\App\Models\ButiranAnggaran $source = null): \App\Models\ButiranAnggaran
    {
        $input = ['tempat_bertugas_id' => $place->id, 'data_sumber' => (object) ['fixture' => $value],
            'semakan_bidaan' => $bid->fresh()->semakan, 'semakan_anggaran' => $estimate->fresh()->semakan,
            'reason' => 'INTERNAL_REASON_NOT_FOR_DISPLAY'];
        if ($source) { $input['semakan_butiran'] = $source->fresh()->semakan; }
        return app(\App\Services\DrafButiranAnggaran::class)->simpan($estimate->id, $source?->id, $input);
    }

    private function historyOwnRole(\App\Models\TempatBertugas $place): void
    {
        $this->operator->penugasanPeranan()->delete();
        $this->operator->forceFill(['tempat_bertugas_id' => $place->id])->save();
        $this->operator->penugasanPeranan()->create(['peranan' => Peranan::PenyediaAnggaran,
            'ptj_id' => $this->owner->id, 'aktiviti_kewangan_id' => $this->activity->id, 'tempat_bertugas_id' => $place->id]);
    }

    public function test_history_shows_real_activation_and_draft_audit_metadata_newest_first_without_payloads(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $place = $this->workspacePlace('Sektor Sejarah');
        $source = $this->historySave($bid, $estimate, $place, 'RAW_SNAPSHOT_SECRET');
        $this->historySave($bid, $estimate, $place, 'RAW_SNAPSHOT_SECRET_UPDATED', $source);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $response = $this->get($url)->assertOk()->assertSee('Log Tindakan')->assertSee('3 rekod dalam skop anda')
            ->assertSee('OS diaktifkan')->assertSee('Draf Butiran ditambah')->assertSee('Draf Butiran dipinda')
            ->assertSee('Penyedia Anggaran')->assertSee($this->operator->name)
            ->assertDontSee('RAW_SNAPSHOT_SECRET')->assertDontSee('INTERNAL_REASON_NOT_FOR_DISPLAY');
        $history = $response->viewData('history');
        $this->assertSame(['source.draft_updated', 'source.draft_created', 'estimate.activated'],
            $history->pluck('tindakan')->all());
        foreach ($history as $event) {
            foreach (['sebelum', 'selepas', 'alasan', 'skop_pelaku'] as $field) {
                $this->assertArrayNotHasKey($field, $event->getAttributes());
            }
            $this->assertArrayNotHasKey('email', $event->pelaku->getAttributes());
        }
        $audit = RekodAudit::where('tindakan', 'source.draft_updated')->sole();
        $this->assertStringContainsString('RAW_SNAPSHOT_SECRET_UPDATED', $audit->selepas['data_sumber_json']);
        $this->assertSame('INTERNAL_REASON_NOT_FOR_DISPLAY', $audit->alasan);
        $this->get($url)->assertOk();
        $this->assertDatabaseCount('rekod_audit', 3);
        $this->workspaceCapture('history-populated', $response);
    }

    public function test_history_empty_state_does_not_invent_events_for_legacy_fixture_rows(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->workspaceEstimate($bid);
        $this->workspaceSource($estimate, $this->workspacePlace('Sektor Tanpa Log'));
        $response = $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
            ->assertSee('Tiada Log Tindakan dalam skop anda.');
        $this->assertSame(0, $response->viewData('history')->total());
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->workspaceCapture('history-empty', $response);
    }

    public function test_history_own_scope_requires_current_and_both_event_workplaces(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $own = $this->workspacePlace('Sektor Own History');
        $other = $this->workspacePlace('Foreign History Sector');
        $stay = $this->historySave($bid, $estimate, $own, 'stay');
        $this->historySave($bid, $estimate, $own, 'stay updated', $stay);
        $movedIn = $this->historySave($bid, $estimate, $other, 'foreign');
        $this->historySave($bid, $estimate, $own, 'moved in', $movedIn);
        $movedOut = $this->historySave($bid, $estimate, $own, 'was own');
        $this->historySave($bid, $estimate, $other, 'moved away', $movedOut);
        $this->historyOwnRole($own);
        $response = $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
            ->assertDontSee('Foreign History Sector')->assertDontSee('RAW_SNAPSHOT_SECRET');
        $history = $response->viewData('history');
        $this->assertSame(3, $history->total());
        $this->assertSame(['source.draft_updated', 'source.draft_created', 'estimate.activated'],
            $history->pluck('tindakan')->all());
        $this->assertSame([(string) $stay->id], $history->where('jenis_entiti', \App\Models\ButiranAnggaran::class)
            ->pluck('id_entiti')->unique()->values()->all());
        $this->assertSame(2, $response->viewData('sources')->total());
        $this->assertDatabaseCount('rekod_audit', 7);
        $this->workspaceCapture('history-workplace', $response);
    }

    public function test_history_pagination_is_independent_and_preserves_both_page_choices(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $place = $this->workspacePlace('Sektor Halaman Log');
        $source = null;
        for ($i = 0; $i < 26; $i++) {
            $source = $this->historySave($bid, $estimate, $place, 'version '.$i, $source);
        }
        for ($i = 0; $i < 25; $i++) { $this->workspaceSource($estimate, $place); }
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $response = $this->get($url.'?page=2&audit_page=2')->assertOk();
        $this->assertSame(2, $response->viewData('sources')->currentPage());
        $this->assertCount(1, $response->viewData('sources')->items());
        $this->assertSame(27, $response->viewData('history')->total());
        $this->assertSame(2, $response->viewData('history')->currentPage());
        $this->assertCount(2, $response->viewData('history')->items());
        $this->assertStringContainsString('page=2', $response->viewData('history')->previousPageUrl());
        $this->get($url.'?page=2&audit_page=999')
            ->assertRedirect(route('estimates.show', [$bid->id, $estimate->id, 'page' => 2, 'audit_page' => 2]));
        $this->get($url.'?page=999&audit_page=2')
            ->assertRedirect(route('estimates.show', [$bid->id, $estimate->id, 'page' => 2, 'audit_page' => 2]));
        $this->assertDatabaseCount('rekod_audit', 27);
        $this->workspaceCapture('history-page', $response);
    }

    public function test_history_monitor_keeps_recorded_actor_role_and_has_no_mutation_controls(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PemantauBidaan);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $response = $this->get($url)->assertOk()->assertSee('OS diaktifkan')->assertSee('Penyedia Anggaran')
            ->assertDontSee('Padam Log')->assertDontSee('Pinda Log')->assertDontSee('INTERNAL_REASON_NOT_FOR_DISPLAY');
        $this->assertSame(Peranan::PenyediaAnggaran, $response->viewData('history')->first()->peranan);
        $this->assertDatabaseCount('rekod_audit', 1);
        $this->workspaceCapture('history-readonly', $response);
    }

    public function test_history_excludes_unrelated_entity_types_cycles_and_unsupported_actions(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $place = $this->workspacePlace('Sektor Event Scope');
        $source = $this->historySave($bid, $estimate, $place, 'source');
        $otherCycle = Kitaran::create(['tahun' => 2029]);
        $payload = ['anggaran_id' => $estimate->id, 'bidaan_id' => $bid->id, 'tempat_bertugas_id' => $place->id];
        foreach ([
            ['source.draft_created', User::class, $this->cycle->id],
            ['source.draft_created', \App\Models\ButiranAnggaran::class, $otherCycle->id],
            ['unsupported.fixture', \App\Models\ButiranAnggaran::class, $this->cycle->id],
        ] as [$action, $type, $cycle]) {
            app(\App\Services\JejakAudit::class)->jalankan(Peranan::PenyediaAnggaran, fn () => new \App\Audit\PeristiwaAudit(
                $action, $type, (string) $source->id, ['exists' => false], $payload, 'Internal fixture only',
                ptjId: $bid->ptj_id, aktivitiId: $bid->aktiviti_kewangan_id, tempatBertugasId: $place->id, kitaranId: $cycle));
        }
        $response = $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
            ->assertDontSee('unsupported.fixture')->assertDontSee('Internal fixture only');
        $this->assertSame(2, $response->viewData('history')->total());
        $this->assertDatabaseCount('rekod_audit', 5);
    }

    public function test_history_other_os_events_never_mix_with_the_open_estimate(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        TetapanAksesPtj::create(['kategori_ptj' => 'JPNT', 'aktiviti_kewangan_id' => $this->activity->id,
            'rujukan_os_id' => $this->unconfigured->id, 'dibenarkan' => true]);
        $otherEstimate = app(\App\Services\PengaktifanKewangan::class)->aktifkanOs($bid->id,
            $this->unconfigured->id, $bid->fresh()->semakan);
        $this->historySave($bid, $otherEstimate, $this->workspacePlace('Other OS sector'), 'other OS source');
        $response = $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk();
        $this->assertSame(1, $response->viewData('history')->total());
        $this->assertSame((string) $estimate->id, $response->viewData('history')->first()->id_entiti);
        $this->assertDatabaseCount('rekod_audit', 3);
    }

    public function test_history_names_are_escaped_and_superadmin_keeps_actual_recorded_role(): void
    {
        $bid = $this->workspaceBid();
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::Superadmin);
        $this->operator->forceFill(['name' => '<script>alert("audit")</script>'])->save();
        $this->phase->forceFill(['dibuka' => false])->save();
        TetapanAksesPtj::query()->update(['dibenarkan' => false]);
        $estimate = $this->historyActivate($bid);
        $response = $this->get(route('estimates.show', [$bid->id, $estimate->id]))->assertOk()
            ->assertSee('<script>alert("audit")</script>')->assertDontSee('<script>alert("audit")</script>', false)
            ->assertSee('Superadmin')->assertDontSee('INTERNAL_REASON_NOT_FOR_DISPLAY');
        $event = $response->viewData('history')->first();
        $this->assertSame($this->operator->id, $event->pelaku_id);
        $this->assertSame(Peranan::Superadmin, $event->peranan);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_history_access_rechecks_central_role_and_session_and_rejects_bad_page_inputs(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $this->from($url)->get($url.'?audit_page=0')->assertSessionHasErrors('audit_page');
        $this->from($url)->get($url.'?audit_page=bad')->assertSessionHasErrors('audit_page');
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => false]);
        $this->get($url)->assertNotFound();
        TetapanAksesPtj::where('rujukan_os_id', $this->os->id)->update(['dibenarkan' => true]);
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PenyeliaBidaan);
        $this->get($url)->assertForbidden();
        $this->operator->penugasanPeranan()->delete();
        $this->workspaceRole(Peranan::PemantauBidaan);
        session()->put('abm_last_activity', now()->timestamp - 1800);
        $this->get($url)->assertRedirect('/login');
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_history_page_validation_summary_preserves_a_truthful_read_only_view(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $errors = ['default' => ['format' => ':message', 'messages' => ['audit_page' => ['Pilih halaman Log Tindakan yang sah.']]]];
        $response = $this->withSession(['errors' => $errors])->get(route('estimates.show', [$bid->id, $estimate->id]))
            ->assertOk()->assertSee('role="alert"', false)->assertSee('Pilih halaman Log Tindakan yang sah.');
        $this->workspaceCapture('history-validation', $response);
    }

    public function test_draft_copy_action_is_scoped_and_rendered_without_private_lineage_or_payloads(): void
    {
        $bid = $this->workspaceBid();
        $estimate = $this->historyActivate($bid);
        $own = $this->workspacePlace('Sektor Salinan');
        $source = $this->historySave($bid, $estimate, $own, 'COPY_SOURCE_PRIVATE_FIXTURE');
        $copy = app(\App\Services\DrafButiranAnggaran::class)->salin($estimate->id, $source->id, [
            'semakan_bidaan' => $bid->fresh()->semakan, 'semakan_anggaran' => $estimate->fresh()->semakan,
            'semakan_butiran' => $source->fresh()->semakan]);
        $url = route('estimates.show', [$bid->id, $estimate->id]);
        $response = $this->get($url)->assertOk()->assertSee('Draf Butiran disalin')
            ->assertDontSee('disalin_daripada')->assertDontSee('COPY_SOURCE_PRIVATE_FIXTURE');
        $this->assertSame(['source.draft_duplicated', 'source.draft_created', 'estimate.activated'],
            $response->viewData('history')->pluck('tindakan')->all());
        $this->workspaceCapture('history-duplicated', $response);
        $other = $this->workspacePlace('Foreign original after copy');
        $this->historySave($bid, $estimate, $other, 'COPY_SOURCE_PRIVATE_FIXTURE', $source);
        $this->historyOwnRole($own);
        $scoped = $this->get($url)->assertOk()->assertSee('Draf Butiran disalin')
            ->assertDontSee('Foreign original after copy')->assertDontSee('disalin_daripada');
        $this->assertSame(1, $scoped->viewData('sources')->total());
        $this->assertSame(2, $scoped->viewData('history')->total());
        $event = $scoped->viewData('history')->first();
        $this->assertSame((string) $copy->id, $event->id_entiti);
        $this->assertArrayNotHasKey('selepas', $event->getAttributes());
        $this->assertDatabaseCount('rekod_audit', 4);
    }

}
