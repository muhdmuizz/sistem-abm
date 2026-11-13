<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Models\PenugasanPeranan;
use App\Models\Ptj;
use App\Models\User;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\OsReferenceSeeder;
use Database\Seeders\TempatBertugasReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FirstInterfaceTest extends TestCase
{
    use RefreshDatabase;

    private function registered(Peranan $role = Peranan::Superadmin): User
    {
        $this->seed([OrganisationalReferenceSeeder::class, TempatBertugasReferenceSeeder::class, OsReferenceSeeder::class]);
        $user = User::factory()->create(['name' => 'Pegawai Rujukan', 'email' => 'registered@example.test']);
        $user->forceFill(['is_active' => true, 'ptj_id' => Ptj::where('kategori', 'JPNT')->firstOrFail()->id])->save();
        PenugasanPeranan::create(['user_id' => $user->id, 'peranan' => $role]);
        return $user;
    }

    private function signedIn(User $user): void
    {
        $user->forceFill(['google_subject' => 'verified-subject'])->save();
        $this->actingAs($user)->withSession(['abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp]);
    }

    private function google(array $profile = [], bool $tokenFailure = false): void
    {
        config(['google.client_id' => 'test-client', 'google.client_secret' => 'test-secret',
            'google.redirect_uri' => 'http://localhost/auth/google/callback', 'google.workspace_domains' => ['example.test']]);
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => $tokenFailure ? Http::response(['error_description' => 'private-provider-secret'], 500) : Http::response(['access_token' => 'server-only-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response(array_replace([
                'sub' => 'verified-subject', 'email' => 'registered@example.test', 'email_verified' => true, 'hd' => 'example.test',
            ], $profile)),
        ]);
    }

    private function googleCallback(string $state = 'valid-state')
    {
        return $this->withSession(['google_flow' => ['state' => 'valid-state', 'verifier' => str_repeat('a', 64), 'issued_at' => now()->timestamp]])
            ->get('/auth/google/callback?code=server-code&state='.$state);
    }

    public function test_public_login_and_protected_reference_pages_have_no_anonymous_data_access(): void
    {
        config(['google.client_id' => null]);
        $this->get('/')->assertRedirect('/login');
        $login = $this->get('/login')->assertOk()->assertSee('Log masuk dengan Google')->assertSee('belum disediakan');
        $this->get('/pentadbiran/rujukan/ptj')->assertRedirect('/login');
        if (getenv('ABM_UI_CAPTURE') === '1') {
            $this->capture('login', $login->getContent());
        }
    }

    public function test_authorization_redirect_uses_pkce_state_and_google_account_selection(): void
    {
        $this->google();
        $response = $this->get('/auth/google')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('select_account', $query['prompt']);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertSame(session('google_flow.state'), $query['state']);
        $this->assertNotEmpty($query['code_challenge']);
        Http::assertNothingSent();
    }

    public function test_callback_binds_verified_identity_and_audits_the_real_registered_user(): void
    {
        $user = $this->registered();
        $this->google();
        $this->googleCallback()->assertRedirect('/utama');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('verified-subject', $user->fresh()->google_subject);
        $this->assertDatabaseHas('rekod_audit', ['pelaku_id' => $user->id, 'tindakan' => 'auth.google.signed_in']);
        $this->assertIsInt(session('abm_authenticated_at'));
        $this->assertArrayNotHasKey('google_subject', $user->fresh()->toArray());
        $this->get('/auth/google/callback?code=server-code&state=valid-state')->assertRedirect('/login');
    }

    public function test_invalid_state_does_not_exchange_code_or_create_accounts(): void
    {
        $this->google();
        $this->googleCallback('forged')->assertRedirect('/login');
        Http::assertNothingSent();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_expired_oauth_flow_is_rejected_without_provider_request(): void
    {
        $this->google();
        $this->withSession(['google_flow' => ['state' => 'valid-state', 'verifier' => str_repeat('a', 64), 'issued_at' => now()->subMinutes(11)->timestamp]])
            ->get('/auth/google/callback?code=server-code&state=valid-state')->assertRedirect('/login');
        Http::assertNothingSent();
    }

    public function test_provider_failure_does_not_expose_tokens_or_raw_provider_messages(): void
    {
        $this->registered();
        $this->google(tokenFailure: true);
        $this->googleCallback()->assertRedirect('/login')->assertSessionHasErrors('login');
        $this->assertStringNotContainsString('private-provider-secret', session('errors')->first('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_unregistered_or_disabled_accounts_are_denied_without_provisioning(): void
    {
        $this->google();
        $this->googleCallback()->assertRedirect('/login');
        $this->assertDatabaseCount('users', 0);
        $user = $this->registered();
        $user->forceFill(['is_active' => false])->save();
        $this->googleCallback()->assertRedirect('/login');
        $this->assertGuest();
        $this->assertNull($user->fresh()->google_subject);
    }

    #[DataProvider('ineligibleProfiles')]
    public function test_untrusted_workspace_or_unverified_email_cannot_sign_in(array $profile): void
    {
        $this->registered();
        $this->google($profile);
        $this->googleCallback()->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public static function ineligibleProfiles(): array
    {
        return [
            'wrong hosted domain' => [['hd' => 'other.test']],
            'missing hosted domain' => [['hd' => '']],
            'unverified email' => [['email_verified' => false]],
        ];
    }

    public function test_subject_mismatch_and_missing_roles_are_rejected(): void
    {
        $user = $this->registered();
        $this->google();
        $user->forceFill(['google_subject' => 'another-subject'])->save();
        $this->googleCallback()->assertRedirect('/login');
        $user->forceFill(['google_subject' => null])->save();
        $user->penugasanPeranan()->delete();
        $this->googleCallback()->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_superadmin_views_reference_pages_with_real_data_and_pagination(): void
    {
        $user = $this->registered();
        $this->signedIn($user);
        $home = $this->get('/utama')->assertOk()->assertSee('Rujukan pentadbiran');
        $this->assertStringContainsString('no-store', $home->headers->get('Cache-Control'));
        $ptj = $this->get('/pentadbiran/rujukan/ptj')->assertOk()->assertSee('69 rekod')->assertSee('Halaman 1 daripada 3');
        $this->get('/pentadbiran/rujukan/aktiviti')->assertOk()->assertSee('Pengurusan Pentadbiran Negeri');
        $this->get('/pentadbiran/rujukan/tempat-bertugas')->assertOk()->assertSee('579 rekod');
        $os = $this->get('/pentadbiran/rujukan/os')->assertOk()->assertSee('33 rekod');
        $this->get('/pentadbiran/rujukan/os?page=2')->assertOk()->assertSee('Dikecualikan');
        if (getenv('ABM_UI_CAPTURE') === '1') {
            foreach (['home' => $home, 'ptj' => $ptj, 'os' => $os] as $name => $response) {
                $this->capture($name, $response->getContent());
            }
        }
    }

    public function test_non_admin_and_revoked_role_cannot_read_global_reference_data(): void
    {
        $user = $this->registered(Peranan::PemantauBidaan);
        $this->signedIn($user);
        $this->get('/utama')->assertOk()->assertDontSee('Rujukan pentadbiran');
        $this->get('/pentadbiran/rujukan/ptj')->assertForbidden();
        $user->penugasanPeranan()->delete();
        $this->get('/utama')->assertRedirect('/login');
    }

    public function test_idle_and_absolute_expiry_are_server_enforced(): void
    {
        $user = $this->registered();
        $this->signedIn($user);
        $this->withSession(['abm_last_activity' => now()->subMinutes(30)->timestamp])->get('/utama')->assertRedirect('/login');
        $this->signedIn($user);
        $this->withSession(['abm_authenticated_at' => now()->subHours(8)->timestamp])->get('/utama')->assertRedirect('/login');
    }

    public function test_explicit_renewal_does_not_reset_absolute_authentication_time(): void
    {
        $user = $this->registered();
        $this->signedIn($user);
        $initial = now()->subHours(2)->timestamp;
        $this->withSession(['abm_authenticated_at' => $initial])->post('/sesi/teruskan')->assertRedirect('/utama');
        $this->assertSame($initial, session('abm_authenticated_at'));
        $user->forceFill(['is_active' => false])->save();
        $this->get('/utama')->assertRedirect('/login');
    }

    public function test_logout_is_post_only_and_invalidates_the_current_session(): void
    {
        $user = $this->registered();
        $this->signedIn($user);
        $this->get('/logout')->assertStatus(405);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertNull(session('abm_authenticated_at'));
    }

    private function capture(string $name, string $html): void
    {
        $directory = storage_path('app/ui-qa');
        if (! is_dir($directory)) { mkdir($directory, 0775, true); }
        file_put_contents($directory.'/'.$name.'.html', str_replace('http://localhost', 'http://127.0.0.1:4173', $html));
        copy(public_path('abm.css'), $directory.'/abm.css');
        copy(public_path('abm.js'), $directory.'/abm.js');
    }
}
