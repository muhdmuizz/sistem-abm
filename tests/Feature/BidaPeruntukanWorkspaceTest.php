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
}
