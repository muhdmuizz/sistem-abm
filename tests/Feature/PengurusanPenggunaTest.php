<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\PenugasanPeranan;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\TempatBertugas;
use App\Models\User;
use App\Services\JejakAudit;
use App\Services\PengurusanPengguna;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\TempatBertugasReferenceSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class PengurusanPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Ptj $jpnt;
    private TempatBertugas $place;
    private AktivitiKewangan $activity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([OrganisationalReferenceSeeder::class, TempatBertugasReferenceSeeder::class]);
        $this->jpnt = Ptj::where('kategori', 'JPNT')->firstOrFail();
        $this->place = TempatBertugas::where('ptj_id', $this->jpnt->id)->where('nama', 'Sektor Pengurusan')->firstOrFail();
        $this->activity = AktivitiKewangan::firstOrFail();
        $this->admin = $this->registeredUser(Peranan::Superadmin);
        $this->authenticated($this->admin);
    }

    private function registeredUser(Peranan $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_active' => true, 'ptj_id' => $this->jpnt->id,
            'tempat_bertugas_id' => $this->place->id, 'google_subject' => 'google-'.$user->id])->save();
        $tuple = ['peranan' => $role];
        if (in_array($role, [Peranan::KetuaBidaan, Peranan::PenyeliaBidaan, Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran])) {
            $tuple['aktiviti_kewangan_id'] = $this->activity->id;
        }
        if (in_array($role, [Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran])) { $tuple['ptj_id'] = $this->jpnt->id; }
        $user->penugasanPeranan()->create($tuple);
        return $user->fresh();
    }

    private function authenticated(User $user, ?int $revision = null): void
    {
        $this->actingAs($user)->withSession([
            'abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp,
            'abm_access_revision' => $revision ?? $user->access_revision,
        ]);
    }

    private function registrationInput(array $replace = []): array
    {
        return array_replace([
            'name' => 'Pegawai Baharu', 'email' => 'new@example.test', 'is_active' => '1',
            'ptj_id' => $this->jpnt->id, 'tempat_bertugas_id' => $this->place->id,
            'roles' => ['penyedia_anggaran' => ['enabled' => '1', 'rows' => [[
                'selected' => '1', 'ptj_id' => $this->jpnt->id, 'aktiviti_kewangan_id' => $this->activity->id,
                'tempat_bertugas_id' => $this->place->id,
            ]]]],
            'reason' => 'Penugasan yang diluluskan.',
        ], $replace);
    }

    private function editInput(User $user, array $replace = []): array
    {
        return $this->registrationInput(array_replace([
            'name' => $user->name, 'email' => $user->email, 'access_revision' => $user->access_revision,
            'roles' => ['pemantau_bidaan' => ['enabled' => '1', 'rows' => [['selected' => '1']]]],
        ], $replace));
    }

    public function test_superadmin_registers_exact_scope_with_attributed_audit_and_no_credentials(): void
    {
        $this->post('/pentadbiran/pengguna', $this->registrationInput())->assertRedirect();
        $user = User::where('email', 'new@example.test')->sole();
        $this->assertTrue($user->is_active);
        $this->assertNull($user->password);
        $this->assertNull($user->google_subject);
        $this->assertNull($user->email_verified_at);
        $this->assertSame($this->place->id, $user->tempat_bertugas_id);
        $assignment = $user->penugasanPeranan()->sole();
        $this->assertSame(Peranan::PenyediaAnggaran, $assignment->peranan);
        $this->assertSame($this->activity->id, $assignment->aktiviti_kewangan_id);
        $this->assertSame($this->place->id, $assignment->tempat_bertugas_id);
        $event = RekodAudit::sole();
        $this->assertSame($this->admin->id, $event->pelaku_id);
        $this->assertSame('access.user_registered', $event->tindakan);
        $this->assertSame('Penugasan yang diluluskan.', $event->alasan);
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_each_non_admin_role_is_denied_list_create_edit_and_session_revocation(): void
    {
        foreach (array_filter(Peranan::cases(), fn ($r) => $r !== Peranan::Superadmin) as $role) {
            $actor = $this->registeredUser($role);
            $this->authenticated($actor);
            $this->get('/pentadbiran/pengguna')->assertForbidden();
            $this->get('/pentadbiran/pengguna/tambah')->assertForbidden();
            $this->get('/pentadbiran/pengguna/'.$this->admin->id.'/pinda')->assertForbidden();
            $this->post('/pentadbiran/pengguna', $this->registrationInput())->assertForbidden();
            $this->put('/pentadbiran/pengguna/'.$this->admin->id, $this->editInput($this->admin))->assertForbidden();
            $this->post('/pentadbiran/pengguna/'.$this->admin->id.'/tamatkan-sesi', ['access_revision' => 0, 'reason' => 'Test'])->assertForbidden();
        }
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_service_rechecks_a_revoked_actor_instead_of_trusting_preloaded_authority(): void
    {
        $this->admin->penugasanPeranan()->delete();
        try {
            app(PengurusanPengguna::class)->simpan(null, $this->registrationInput());
            $this->fail('Revoked actor was allowed.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseMissing('users', ['email' => 'new@example.test']);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_invalid_organisation_role_scope_or_duplicate_tuple_is_rejected_atomically(): void
    {
        $otherPlace = TempatBertugas::where('ptj_id', $this->jpnt->id)->whereKeyNot($this->place->id)->firstOrFail();
        $ppd = Ptj::where('kategori', 'PPD')->firstOrFail();
        $tuple = ['selected' => 1, 'ptj_id' => $this->jpnt->id, 'aktiviti_kewangan_id' => $this->activity->id, 'tempat_bertugas_id' => $this->place->id];
        $payloads = [
            ['ptj_id' => $ppd->id],
            ['roles' => ['ketua_bidaan' => ['enabled' => 1, 'rows' => [['selected' => 1, 'aktiviti_kewangan_id' => $this->activity->id]]]], 'ptj_id' => $ppd->id, 'tempat_bertugas_id' => null],
            ['roles' => ['penyedia_anggaran' => ['enabled' => 1, 'rows' => [array_replace($tuple, ['tempat_bertugas_id' => $otherPlace->id])]]]],
            ['roles' => ['penyedia_anggaran' => ['enabled' => 1, 'rows' => [$tuple, $tuple]]]],
            ['roles' => ['penyedia_bidaan' => ['enabled' => 1, 'rows' => [['selected' => 1, 'ptj_id' => $this->jpnt->id]]]]],
            ['roles' => ['superadmin' => ['enabled' => 1, 'rows' => [$tuple]]]],
            ['roles' => ['pemantau_bidaan' => ['enabled' => 1, 'rows' => [$tuple]]]],
            ['roles' => ['new_role' => ['enabled' => 1]]],
            ['roles' => ['penyelia_bidaan' => ['enabled' => 1, 'rows' => []]]],
        ];
        foreach ($payloads as $overrides) {
            $this->post('/pentadbiran/pengguna', $this->registrationInput($overrides))->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_unchecked_parent_does_not_grant_selected_children_and_active_account_can_await_assignments(): void
    {
        $input = $this->registrationInput();
        unset($input['roles']['penyedia_anggaran']['enabled']);
        $this->post('/pentadbiran/pengguna', $input)->assertRedirect();
        $user = User::where('email', 'new@example.test')->sole();
        $this->assertSame(0, $user->penugasanPeranan()->count());
        $this->assertNull($user->google_subject);
    }

    public function test_multiple_role_tuples_are_preserved_without_cross_combination(): void
    {
        $second = AktivitiKewangan::whereKeyNot($this->activity->id)->firstOrFail();
        $ppd = Ptj::where('kategori', 'PPD')->firstOrFail();
        $input = $this->registrationInput(['roles' => [
            'penyedia_bidaan' => ['enabled' => 1, 'rows' => [['selected' => 1, 'ptj_id' => $ppd->id, 'aktiviti_kewangan_id' => $this->activity->id]]],
            'penyedia_anggaran' => ['enabled' => 1, 'rows' => [['selected' => 1, 'ptj_id' => $this->jpnt->id, 'aktiviti_kewangan_id' => $second->id]]],
        ]]);
        $this->post('/pentadbiran/pengguna', $input)->assertRedirect();
        $user = User::where('email', 'new@example.test')->sole();
        $this->assertSame(2, $user->penugasanPeranan()->count());
        $this->assertFalse(app(\App\Services\SkopPeranan::class)->meliputi($user->id, Peranan::PenyediaBidaan, $this->jpnt->id, $second->id));
        $this->assertTrue(app(\App\Services\SkopPeranan::class)->meliputi($user->id, Peranan::PenyediaBidaan, $ppd->id, $this->activity->id));
    }

    public function test_stale_editor_is_conflicted_and_retained_tuple_keeps_its_id(): void
    {
        $target = $this->registeredUser(Peranan::PemantauBidaan);
        $assignment = $target->penugasanPeranan()->sole();
        $old = $this->editInput($target, ['name' => 'First change']);
        $this->put('/pentadbiran/pengguna/'.$target->id, $old)->assertRedirect();
        $this->assertSame($assignment->id, $target->penugasanPeranan()->sole()->id);
        $this->put('/pentadbiran/pengguna/'.$target->id, array_replace($old, ['name' => 'Stale change']))->assertConflict();
        $this->assertSame('First change', $target->fresh()->name);
        $this->assertSame(1, $target->fresh()->access_revision);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_deactivation_revokes_all_database_sessions_and_rejects_old_session_snapshots(): void
    {
        $target = $this->registeredUser(Peranan::PemantauBidaan);
        foreach (['device-a', 'device-b'] as $id) {
            DB::table('sessions')->insert(['id' => $id, 'user_id' => $target->id, 'payload' => '', 'last_activity' => now()->timestamp]);
        }
        $this->put('/pentadbiran/pengguna/'.$target->id, $this->editInput($target, ['is_active' => 0]))->assertRedirect();
        $this->assertFalse($target->fresh()->is_active);
        $this->assertSame(1, $target->fresh()->access_revision);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $target->id)->count());
        $this->authenticated($target->fresh(), 0);
        $this->get('/utama')->assertRedirect('/login');
    }

    public function test_admin_can_revoke_sessions_without_disabling_target_and_other_users_remain_unchanged(): void
    {
        $target = $this->registeredUser(Peranan::PemantauBidaan);
        $this->post('/pentadbiran/pengguna/'.$target->id.'/tamatkan-sesi', ['access_revision' => 0, 'reason' => 'Semakan keselamatan.'])->assertRedirect();
        $this->assertTrue($target->fresh()->is_active);
        $this->assertSame(1, $target->fresh()->access_revision);
        $this->assertSame(0, $this->admin->fresh()->access_revision);
        $this->assertDatabaseHas('rekod_audit', ['tindakan' => 'access.sessions_revoked', 'pelaku_id' => $this->admin->id, 'id_entiti' => (string) $target->id]);
        foreach (['device-a', 'device-b'] as $device) {
            $this->authenticated($target->fresh(), 0);
            $this->get('/utama')->assertRedirect('/login');
        }
    }

    public function test_ordinary_user_can_only_sign_out_all_their_own_devices(): void
    {
        $user = $this->registeredUser(Peranan::PemantauBidaan);
        $this->authenticated($user);
        $this->post('/sesi/keluar-semua', ['user_id' => $this->admin->id])->assertRedirect('/login');
        $this->assertGuest();
        $this->assertSame(1, $user->fresh()->access_revision);
        $this->assertSame(0, $this->admin->fresh()->access_revision);
        $this->assertDatabaseHas('rekod_audit', ['tindakan' => 'auth.all_devices_signed_out', 'pelaku_id' => $user->id]);
    }

    public function test_editing_own_account_ends_current_session_after_atomic_audit(): void
    {
        $input = $this->editInput($this->admin, ['roles' => ['superadmin' => ['enabled' => 1]]]);
        $this->put('/pentadbiran/pengguna/'.$this->admin->id, $input)->assertRedirect('/login');
        $this->assertGuest();
        $this->assertSame(1, $this->admin->fresh()->access_revision);
        $this->assertDatabaseHas('rekod_audit', ['tindakan' => 'access.user_updated', 'pelaku_id' => $this->admin->id]);
    }

    public function test_failed_audit_rolls_back_registration_and_all_role_changes(): void
    {
        $this->mock(JejakAudit::class, function ($mock) {
            $mock->shouldReceive('jalankan')->once()->andReturnUsing(function ($role, $operation) {
                $operation();
                throw new RuntimeException('Audit unavailable.');
            });
        });
        try {
            app(PengurusanPengguna::class)->simpan(null, $this->registrationInput());
            $this->fail('Audit failure was ignored.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable.', $exception->getMessage());
        }
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('penugasan_peranan', 1);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_case_insensitive_email_uniqueness_and_sensitive_field_injection_are_rejected_or_ignored(): void
    {
        $this->post('/pentadbiran/pengguna', $this->registrationInput(['email' => strtoupper($this->admin->email)]))->assertSessionHasErrors('email');
        $this->post('/pentadbiran/pengguna', $this->registrationInput([
            'google_subject' => 'forged', 'password' => 'forged', 'email_verified_at' => now()->toDateTimeString(),
        ]))->assertRedirect();
        $target = User::where('email', 'new@example.test')->sole();
        $this->assertNull($target->google_subject);
        $this->assertNull($target->password);
        $this->assertNull($target->email_verified_at);
        $this->assertStringNotContainsString('forged', json_encode(RekodAudit::sole()->toArray()));
    }

    public function test_database_rejects_case_variant_duplicate_even_outside_administration_service(): void
    {
        $this->expectException(QueryException::class);
        User::create(['name' => 'Duplicate', 'email' => strtoupper($this->admin->email)]);
    }

    public function test_old_sessions_without_revision_are_invalidated_and_fresh_revision_remains_valid(): void
    {
        $this->withSession(['abm_access_revision' => null])->get('/utama')->assertRedirect('/login');
        $this->authenticated($this->admin);
        $this->get('/utama')->assertOk();
    }

    public function test_forms_search_pagination_and_escaped_identity_are_rendered_without_secret_values(): void
    {
        $response = $this->get('/pentadbiran/pengguna')->assertOk()->assertSee('Pengguna &amp; Peranan', false);
        $form = $this->get('/pentadbiran/pengguna/tambah')->assertOk()->assertSee('Alasan perubahan')->assertSee('Tambah Penugasan');
        $this->get('/pentadbiran/pengguna?q=absent-value')->assertOk()->assertSee('Tiada pengguna ditemui.');
        $this->get('/pentadbiran/pengguna/'.$this->admin->id.'/pinda')->assertOk()->assertSee('Tamatkan Semua Sesi')->assertDontSee($this->admin->google_subject);
        User::factory()->count(27)->create();
        $this->get('/pentadbiran/pengguna?page=2')->assertOk()->assertSee('Halaman 2 daripada 2');
        if (getenv('ABM_UI_CAPTURE') === '1') {
            $directory = storage_path('app/ui-qa');
            if (! is_dir($directory)) { mkdir($directory, 0775, true); }
            foreach (['users' => $response, 'user-form' => $form] as $name => $page) {
                file_put_contents($directory.'/'.$name.'.html', str_replace('http://localhost', 'http://127.0.0.1:4173', $page->getContent()));
            }
        }
    }
}
