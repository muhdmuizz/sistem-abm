<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Models\PenugasanPeranan;
use App\Models\RekodAudit;
use App\Models\User;
use App\Services\JejakAudit;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\TempatBertugasReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class BootstrapLocalSuperadminTest extends TestCase
{
    use RefreshDatabase;

    private function options(array $overrides = []): array
    {
        return array_replace([
            '--email' => 'operator@example.test', '--name' => 'Real local operator',
            '--ptj-code' => '41620101', '--workplace' => 'Sektor Pengurusan',
            '--reason' => 'Initial local setup by the declared operator', '--confirm' => true,
        ], $overrides);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([OrganisationalReferenceSeeder::class, TempatBertugasReferenceSeeder::class]);
    }

    public function test_initial_registration_is_audited_without_a_password_google_identity_or_web_session(): void
    {
        $this->artisan('abm:bootstrap-local-superadmin', $this->options())->assertExitCode(0);
        $user = User::sole();
        $this->assertTrue($user->is_active);
        $this->assertSame('41620101', $user->ptj->kod_ptj);
        $this->assertSame('Sektor Pengurusan', $user->tempatBertugas->nama);
        $this->assertSame($user->ptj_id, $user->tempatBertugas->ptj_id);
        $this->assertNull($user->password);
        $this->assertNull($user->google_subject);
        $this->assertNull($user->email_verified_at);
        $role = PenugasanPeranan::sole();
        $this->assertSame(Peranan::Superadmin, $role->peranan);
        $this->assertNull($role->ptj_id);
        $this->assertNull($role->aktiviti_kewangan_id);
        $event = RekodAudit::sole();
        $this->assertSame($user->id, $event->pelaku_id);
        $this->assertSame('access.local_superadmin_bootstrapped', $event->tindakan);
        $this->assertSame('local_console_operator_declaration', $event->selepas['identity_assurance']);
        $this->assertFalse($event->selepas['google_verified']);
        $this->assertSame(['superadmin'], $event->selepas['roles']);
        $this->assertGuest();
        $this->get('/utama')->assertRedirect('/login');
    }

    public function test_any_existing_superadmin_closes_initialisation_and_reruns_do_not_duplicate_data(): void
    {
        $this->artisan('abm:bootstrap-local-superadmin', $this->options())->assertExitCode(0);
        $this->artisan('abm:bootstrap-local-superadmin', $this->options(['--email' => 'second@example.test']))
            ->expectsOutputToContain('initialisation is closed')->assertExitCode(1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('penugasan_peranan', 1);
        $this->assertDatabaseCount('rekod_audit', 1);
        $this->assertGuest();
    }

    public function test_removing_the_first_role_does_not_reopen_initialisation(): void
    {
        $this->artisan('abm:bootstrap-local-superadmin', $this->options())->assertExitCode(0);
        PenugasanPeranan::query()->delete();
        $this->artisan('abm:bootstrap-local-superadmin', $this->options(['--email' => 'second@example.test']))
            ->expectsOutputToContain('initialisation is closed')->assertExitCode(1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('penugasan_peranan', 0);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    public function test_existing_case_variant_account_is_never_silently_activated_or_promoted(): void
    {
        $user = User::create(['name' => 'Existing account', 'email' => 'OPERATOR@example.test']);
        $this->artisan('abm:bootstrap-local-superadmin', $this->options())
            ->expectsOutputToContain('already exists')->assertExitCode(1);
        $this->assertFalse($user->fresh()->is_active);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('penugasan_peranan', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_wrong_parent_and_missing_workplace_do_not_create_any_account_or_assignment(): void
    {
        foreach ([['--ptj-code' => '41620201'], ['--workplace' => 'Nonexistent Sektor']] as $overrides) {
            $this->artisan('abm:bootstrap-local-superadmin', $this->options($overrides))->assertExitCode(1);
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('penugasan_peranan', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_explicit_operator_declaration_and_valid_identity_and_reason_are_required(): void
    {
        foreach ([['--confirm' => false], ['--email' => 'invalid'], ['--name' => ''], ['--reason' => '']] as $overrides) {
            $this->artisan('abm:bootstrap-local-superadmin', $this->options($overrides))->assertExitCode(1);
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_production_environment_cannot_use_the_local_bootstrap(): void
    {
        $this->app['env'] = 'production';
        try {
            $this->artisan('abm:bootstrap-local-superadmin', $this->options())
                ->expectsOutputToContain('only for local development')->assertExitCode(1);
            $this->assertDatabaseCount('users', 0);
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    public function test_audit_failure_rolls_back_account_and_assignment_and_clears_console_identity(): void
    {
        $this->mock(JejakAudit::class, function ($mock) {
            $mock->shouldReceive('jalankan')->once()->andThrow(new RuntimeException('Audit unavailable.'));
        });
        $this->artisan('abm:bootstrap-local-superadmin', $this->options())->assertExitCode(1);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('penugasan_peranan', 0);
        $this->assertDatabaseCount('rekod_audit', 0);
        $this->assertGuest();
    }

    public function test_bootstrapped_account_still_requires_verified_google_callback_before_access(): void
    {
        $this->artisan('abm:bootstrap-local-superadmin', $this->options())->assertExitCode(0);
        config(['google.client_id' => 'test', 'google.client_secret' => 'test',
            'google.redirect_uri' => 'http://localhost/auth/google/callback', 'google.workspace_domains' => ['example.test']]);
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'server-only']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'verified-sub', 'email' => 'operator@example.test', 'email_verified' => true, 'hd' => 'example.test',
            ]),
        ]);
        $this->get('/utama')->assertRedirect('/login');
        $this->withSession(['google_flow' => ['state' => 'valid', 'verifier' => str_repeat('a', 64), 'issued_at' => now()->timestamp]])
            ->get('/auth/google/callback?state=valid&code=server-code')->assertRedirect('/utama');
        $this->assertSame('verified-sub', User::sole()->google_subject);
        $this->assertDatabaseCount('rekod_audit', 2);
        $this->get('/pentadbiran/rujukan/ptj')->assertOk();
    }
}
