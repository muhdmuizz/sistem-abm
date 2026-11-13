<?php

namespace Tests\Feature;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\PenugasanPeranan;
use App\Models\Ptj;
use App\Models\RekodAudit;
use App\Models\TempatBertugas;
use App\Models\User;
use App\Services\JejakAudit;
use Carbon\CarbonImmutable;
use Database\Seeders\OrganisationalReferenceSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class JejakAuditTest extends TestCase
{
    use RefreshDatabase;

    private function actor(Peranan $role = Peranan::Superadmin): User
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $ptj = Ptj::where('kategori', 'JPNT')->firstOrFail();
        $place = TempatBertugas::firstOrCreate(['ptj_id' => $ptj->id, 'jenis' => 'sektor', 'nama' => 'Sektor Pengurusan']);
        $user = User::factory()->create();
        $user->forceFill(['is_active' => true, 'ptj_id' => $ptj->id, 'tempat_bertugas_id' => $place->id])->save();
        $activity = AktivitiKewangan::firstOrFail()->id;
        PenugasanPeranan::create([
            'user_id' => $user->id, 'peranan' => $role,
            'ptj_id' => in_array($role, [Peranan::PenyediaBidaan, Peranan::PenyediaAnggaran], true) ? $ptj->id : null,
            'aktiviti_kewangan_id' => in_array($role, [Peranan::Superadmin, Peranan::PemantauBidaan], true) ? null : $activity,
        ]);
        $this->actingAs($user);
        return $user;
    }

    private function event(array $after = ['nama' => 'Updated'], ?int $invalidCycle = null): PeristiwaAudit
    {
        $ptj = Ptj::where('kategori', 'JPNT')->firstOrFail();
        return new PeristiwaAudit(
            tindakan: 'ptj.updated', jenisEntiti: Ptj::class, idEntiti: (string) $ptj->id,
            sebelum: ['nama' => 'JPNT'], selepas: $after, alasan: 'Approved correction',
            ptjId: $ptj->id, kitaranId: $invalidCycle,
        );
    }

    public function test_captures_authenticated_actor_role_scope_and_explicit_changes(): void
    {
        $user = $this->actor();
        $record = (new JejakAudit)->jalankan(Peranan::Superadmin, function () {
            Ptj::where('kategori', 'JPNT')->update(['nama' => 'Updated']);
            return $this->event(['nama' => 'Updated', 'amaun' => '1234567.89']);
        });
        $this->assertSame($user->id, $record->pelaku_id);
        $this->assertTrue($record->pelaku->is($user));
        $this->assertSame(Peranan::Superadmin, $record->peranan);
        $this->assertSame($user->ptj_id, $record->skop_pelaku['ptj_id']);
        $this->assertSame($user->tempat_bertugas_id, $record->skop_pelaku['tempat_bertugas_id']);
        $this->assertSame('JPNT', $record->sebelum['nama']);
        $this->assertSame('1234567.89', $record->selepas['amaun']);
        $this->assertSame('Updated', Ptj::findOrFail($user->ptj_id)->nama);
        $this->assertDatabaseCount('rekod_audit', 1);
    }

    #[DataProvider('roles')]
    public function test_all_six_roles_are_attributed_to_the_actual_assigned_role(Peranan $role): void
    {
        $user = $this->actor($role);
        // Attribution fixture only; no business mutation/permission is implied by recording.
        $record = (new JejakAudit)->jalankan($role, fn () => $this->event());
        $this->assertSame($user->id, $record->pelaku_id);
        $this->assertSame($role, $record->peranan);
    }

    public static function roles(): array
    {
        return array_map(fn ($role) => [$role], Peranan::cases());
    }

    public function test_superadmin_records_real_role_when_acting_in_another_functional_scope(): void
    {
        $user = $this->actor();
        $record = (new JejakAudit)->jalankan(Peranan::KetuaBidaan, fn () => $this->event());
        $this->assertSame($user->id, $record->pelaku_id);
        $this->assertSame(Peranan::Superadmin, $record->peranan);
    }

    public function test_anonymous_actor_cannot_invoke_the_callback(): void
    {
        $called = false;
        try {
            (new JejakAudit)->jalankan(Peranan::Superadmin, function () use (&$called) { $called = true; });
            $this->fail('Anonymous audit must fail.');
        } catch (AuthorizationException) {
            $this->assertFalse($called);
        }
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_deactivated_actor_is_reloaded_instead_of_trusting_cached_auth_user(): void
    {
        $user = $this->actor();
        User::whereKey($user->id)->update(['is_active' => false]);
        $this->expectException(AuthorizationException::class);
        (new JejakAudit)->jalankan(Peranan::Superadmin, fn () => $this->event());
    }

    public function test_unassigned_role_is_rejected_before_callback(): void
    {
        $this->actor(Peranan::PemantauBidaan);
        $this->expectException(AuthorizationException::class);
        (new JejakAudit)->jalankan(Peranan::KetuaBidaan, fn () => $this->event());
    }

    public function test_audit_database_failure_rolls_back_associated_business_change(): void
    {
        $user = $this->actor();
        try {
            (new JejakAudit)->jalankan(Peranan::Superadmin, function () use ($user) {
                Ptj::whereKey($user->ptj_id)->update(['nama' => 'Must roll back']);
                return $this->event(invalidCycle: 999999);
            });
            $this->fail('Invalid audit foreign key must fail.');
        } catch (QueryException) {
            $this->assertSame('JPNT', Ptj::findOrFail($user->ptj_id)->nama);
        }
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_business_exception_leaves_neither_mutation_nor_audit_record(): void
    {
        $user = $this->actor();
        try {
            (new JejakAudit)->jalankan(Peranan::Superadmin, function () use ($user) {
                Ptj::whereKey($user->ptj_id)->update(['nama' => 'Must roll back']);
                throw new RuntimeException('Business failure');
            });
        } catch (RuntimeException) {
            $this->assertSame('JPNT', Ptj::findOrFail($user->ptj_id)->nama);
        }
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_outer_transaction_rollback_also_removes_nested_audit_insert(): void
    {
        $this->actor();
        try {
            DB::transaction(function () {
                (new JejakAudit)->jalankan(Peranan::Superadmin, fn () => $this->event());
                throw new RuntimeException('Outer operation failed');
            });
        } catch (RuntimeException) {
            $this->assertDatabaseCount('rekod_audit', 0);
        }
    }

    #[DataProvider('sensitiveChanges')]
    public function test_sensitive_changes_are_rejected_and_mutation_is_rolled_back(array $changes): void
    {
        $user = $this->actor();
        try {
            (new JejakAudit)->jalankan(Peranan::Superadmin, function () use ($user, $changes) {
                Ptj::whereKey($user->ptj_id)->update(['nama' => 'Must roll back']);
                return $this->event($changes);
            });
            $this->fail('Sensitive audit changes must fail.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringNotContainsString('private-value', $exception->getMessage());
            $this->assertSame('JPNT', Ptj::findOrFail($user->ptj_id)->nama);
        }
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public static function sensitiveChanges(): array
    {
        return [
            'password' => [['password' => 'private-value']],
            'nested token' => [['nested' => ['accessToken' => 'private-value']]],
            'app key' => [['app_key' => 'private-value']],
        ];
    }

    public function test_required_reason_failure_rolls_back_the_mutation(): void
    {
        $user = $this->actor();
        try {
            (new JejakAudit)->jalankan(Peranan::Superadmin, function () use ($user) {
                Ptj::whereKey($user->ptj_id)->update(['nama' => 'Must roll back']);
                return new PeristiwaAudit('test', Ptj::class, (string) $user->ptj_id, wajibAlasan: true);
            });
            $this->fail('Mandatory reason must be present.');
        } catch (InvalidArgumentException) {
            $this->assertSame('JPNT', Ptj::findOrFail($user->ptj_id)->nama);
        }
        $this->assertDatabaseCount('rekod_audit', 0);
    }

    public function test_timestamp_represents_utc_even_with_non_utc_database_session(): void
    {
        $this->actor();
        DB::statement("SET LOCAL TIME ZONE 'Asia/Kuala_Lumpur'");
        $start = CarbonImmutable::now('UTC')->subSecond();
        $record = (new JejakAudit)->jalankan(Peranan::Superadmin, fn () => $this->event());
        $this->assertTrue($record->berlaku_pada->utc()->between($start, CarbonImmutable::now('UTC')->addSecond()));
    }

    #[DataProvider('modelMutations')]
    public function test_model_rejects_history_mutation(string $action): void
    {
        $this->actor();
        $record = (new JejakAudit)->jalankan(Peranan::Superadmin, fn () => $this->event());
        $this->expectException(LogicException::class);
        if ($action === 'delete') { $record->delete(); } else { $record->forceFill(['tindakan' => 'changed'])->save(); }
    }

    public static function modelMutations(): array
    {
        return [['update'], ['delete']];
    }

    #[DataProvider('databaseMutations')]
    public function test_database_rejects_model_bypass_and_direct_history_mutation(string $action): void
    {
        $this->actor();
        $record = (new JejakAudit)->jalankan(Peranan::Superadmin, fn () => $this->event());
        $this->expectException(QueryException::class);
        match ($action) {
            'update' => DB::table('rekod_audit')->where('id', $record->id)->update(['tindakan' => 'changed']),
            'delete' => DB::table('rekod_audit')->where('id', $record->id)->delete(),
            'truncate' => DB::statement('TRUNCATE TABLE rekod_audit'),
            'quiet update' => $record->forceFill(['tindakan' => 'changed'])->saveQuietly(),
        };
    }

    public static function databaseMutations(): array
    {
        return [['update'], ['delete'], ['truncate'], ['quiet update']];
    }

    public function test_corrections_append_a_new_event_without_changing_previous_evidence(): void
    {
        $this->actor();
        $audit = new JejakAudit;
        $first = $audit->jalankan(Peranan::Superadmin, fn () => $this->event(['nama' => 'First']));
        $second = $audit->jalankan(Peranan::Superadmin, fn () => $this->event(['nama' => 'Corrected']));
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('First', $first->fresh()->selepas['nama']);
        $this->assertSame('Corrected', $second->fresh()->selepas['nama']);
        $this->assertDatabaseCount('rekod_audit', 2);
    }

    public function test_actor_and_role_scope_evidence_survive_assignment_revocation(): void
    {
        $user = $this->actor();
        $record = (new JejakAudit)->jalankan(Peranan::Superadmin, fn () => $this->event());
        $assignmentId = $user->penugasanPeranan()->sole()->id;
        $user->penugasanPeranan()->delete();
        $this->assertSame($assignmentId, $record->fresh()->skop_pelaku['penugasan'][0]['id']);
        $this->expectException(QueryException::class);
        $user->delete();
    }
}
