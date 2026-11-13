<?php

namespace Tests\Feature;

use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\PenugasanPeranan;
use App\Models\Ptj;
use App\Models\TempatBertugas;
use App\Models\User;
use App\Services\SkopPeranan;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\TempatBertugasReferenceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SkopPerananTest extends TestCase
{
    use RefreshDatabase;

    private function references(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->seed(TempatBertugasReferenceSeeder::class);
    }

    private function ptj(string $category = 'PPD'): int
    {
        return Ptj::where('kategori', $category)->orderBy('id')->firstOrFail()->id;
    }

    private function activity(string $name = 'Pengurusan Pentadbiran Negeri'): int
    {
        return AktivitiKewangan::where('nama', $name)->firstOrFail()->id;
    }

    private function person(?int $ptj = null, ?int $workplace = null): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_active' => true, 'ptj_id' => $ptj ?? $this->ptj(), 'tempat_bertugas_id' => $workplace])->save();
        return $user;
    }

    private function assign(User $user, Peranan $role, ?int $ptj = null, ?int $activity = null, ?int $workplace = null): PenugasanPeranan
    {
        return PenugasanPeranan::create([
            'user_id' => $user->id, 'peranan' => $role, 'ptj_id' => $ptj,
            'aktiviti_kewangan_id' => $activity, 'tempat_bertugas_id' => $workplace,
        ]);
    }

    public function test_exactly_six_roles_and_no_automatic_activation_or_assignment(): void
    {
        $this->references();
        $user = User::factory()->create()->fresh();
        $this->assertCount(6, Peranan::cases());
        $this->assertFalse($user->is_active);
        $this->assertDatabaseCount('penugasan_peranan', 0);
        $this->assertFalse((new SkopPeranan)->meliputi($user->id, Peranan::Superadmin, $this->ptj(), $this->activity()));
        $this->assertFalse($user->isFillable('is_active'));
        $this->assertFalse($user->isFillable('ptj_id'));
        $this->assertFalse($user->isFillable('tempat_bertugas_id'));
    }

    public function test_organisational_identity_alone_grants_no_role(): void
    {
        $this->references();
        $place = TempatBertugas::where('ptj_id', $this->ptj())->firstOrFail();
        $user = $this->person($place->ptj_id, $place->id);
        $this->assertTrue($user->tempatBertugas->is($place));
        foreach (Peranan::cases() as $role) {
            $this->assertFalse((new SkopPeranan)->meliputi($user->id, $role, $this->ptj(), $this->activity(), $place->id));
        }
    }

    public function test_preparation_roles_do_not_implicitly_grant_each_other(): void
    {
        $this->references();
        $user = $this->person();
        $scope = new SkopPeranan;
        $this->assign($user, Peranan::PenyediaBidaan, $this->ptj(), $this->activity());
        $this->assertTrue($scope->meliputi($user->id, Peranan::PenyediaBidaan, $this->ptj(), $this->activity()));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity()));
        $this->assign($user, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity());
        $this->assertTrue($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity()));
    }

    public function test_assignments_are_additive_without_combining_unassigned_ptj_activity_pairs(): void
    {
        $this->references();
        $user = $this->person();
        $ptjs = Ptj::where('kategori', 'PPD')->orderBy('id')->take(2)->pluck('id');
        $a = $this->activity();
        $b = $this->activity('Pendidikan Rendah');
        $this->assign($user, Peranan::PenyediaBidaan, $ptjs[0], $a);
        $this->assign($user, Peranan::PenyediaBidaan, $ptjs[1], $b);
        $scope = new SkopPeranan;
        $this->assertTrue($scope->meliputi($user->id, Peranan::PenyediaBidaan, $ptjs[0], $a));
        $this->assertTrue($scope->meliputi($user->id, Peranan::PenyediaBidaan, $ptjs[1], $b));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaBidaan, $ptjs[0], $b));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaBidaan, $ptjs[1], $a));
    }

    public function test_workplace_assignment_is_restricted_to_registered_own_workplace(): void
    {
        $this->references();
        $places = TempatBertugas::where('ptj_id', $this->ptj())->orderBy('id')->take(2)->get();
        $user = $this->person($this->ptj(), $places[0]->id);
        $this->assign($user, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity(), $places[0]->id);
        $scope = new SkopPeranan;
        $this->assertTrue($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity(), $places[0]->id));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity(), $places[1]->id));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity()));
        $user->forceFill(['tempat_bertugas_id' => $places[1]->id])->save();
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity(), $places[0]->id));
    }

    public function test_ptj_assignment_covers_workplaces_without_allowing_foreign_parent_selection(): void
    {
        $this->references();
        $user = $this->person();
        $this->assign($user, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity());
        $scope = new SkopPeranan;
        foreach (TempatBertugas::where('ptj_id', $this->ptj())->take(2)->get() as $place) {
            $this->assertTrue($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity(), $place->id));
        }
        $foreign = TempatBertugas::where('ptj_id', '!=', $this->ptj())->firstOrFail();
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity(), $foreign->id));
    }

    public function test_reviewers_cover_all_ptj_only_within_their_assigned_activity(): void
    {
        $this->references();
        $user = $this->person($this->ptj('JPNT'));
        $scope = new SkopPeranan;
        foreach ([Peranan::KetuaBidaan, Peranan::PenyeliaBidaan] as $role) {
            $this->assign($user, $role, null, $this->activity());
            foreach (['JPNT', 'PPD', 'SMK PTJ'] as $category) {
                $this->assertTrue($scope->meliputi($user->id, $role, $this->ptj($category), $this->activity()));
            }
            $this->assertFalse($scope->meliputi($user->id, $role, $this->ptj(), $this->activity('Pendidikan Rendah')));
        }
    }

    public function test_ketua_requires_current_jpnt_identity_and_penyelia_does_not_become_ketua(): void
    {
        $this->references();
        $user = $this->person($this->ptj('JPNT'));
        $scope = new SkopPeranan;
        $this->assign($user, Peranan::PenyeliaBidaan, null, $this->activity());
        $this->assertFalse($scope->meliputi($user->id, Peranan::KetuaBidaan, $this->ptj(), $this->activity()));
        $this->assign($user, Peranan::KetuaBidaan, null, $this->activity());
        $this->assertTrue($scope->meliputi($user->id, Peranan::KetuaBidaan, $this->ptj(), $this->activity()));
        $user->forceFill(['ptj_id' => $this->ptj()])->save();
        $this->assertFalse($scope->meliputi($user->id, Peranan::KetuaBidaan, $this->ptj(), $this->activity()));
        $this->assertTrue($scope->meliputi($user->id, Peranan::PenyeliaBidaan, $this->ptj(), $this->activity()));
    }

    public function test_monitor_scopes_are_global_or_activity_or_ptj_and_never_grant_preparation(): void
    {
        $this->references();
        $user = $this->person();
        $scope = new SkopPeranan;
        $assignment = $this->assign($user, Peranan::PemantauBidaan, null, $this->activity());
        $this->assertTrue($scope->meliputi($user->id, Peranan::PemantauBidaan, $this->ptj('SMK PTJ'), $this->activity()));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PemantauBidaan, $this->ptj(), $this->activity('Pendidikan Rendah')));
        $assignment->delete();
        $assignment = $this->assign($user, Peranan::PemantauBidaan, $this->ptj());
        $this->assertTrue($scope->meliputi($user->id, Peranan::PemantauBidaan, $this->ptj(), $this->activity('Pendidikan Rendah')));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PemantauBidaan, $this->ptj('SMK PTJ'), $this->activity()));
        $assignment->delete();
        $this->assign($user, Peranan::PemantauBidaan);
        $this->assertTrue($scope->meliputi($user->id, Peranan::PemantauBidaan, $this->ptj('SMK PTJ'), $this->activity('Pendidikan Rendah')));
        $this->assertFalse($scope->meliputi($user->id, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity()));
        $this->assertFalse($scope->meliputi($user->id, Peranan::KetuaBidaan, $this->ptj(), $this->activity()));
    }

    public function test_superadmin_covers_all_roles_without_changing_actor_identity(): void
    {
        $this->references();
        $user = $this->person();
        $this->assign($user, Peranan::Superadmin);
        foreach (Peranan::cases() as $role) {
            $this->assertTrue((new SkopPeranan)->meliputi($user->id, $role, $this->ptj('SMK PTJ'), $this->activity()));
        }
        $this->assertSame($user->id, $user->penugasanPeranan()->sole()->user->id);
    }

    public function test_deactivation_revocation_and_missing_identity_are_rechecked(): void
    {
        $this->references();
        $user = $this->person();
        $assignment = $this->assign($user, Peranan::Superadmin);
        $scope = new SkopPeranan;
        $this->assertTrue($scope->meliputi($user->id, Peranan::Superadmin, $this->ptj(), $this->activity()));
        $user->forceFill(['is_active' => false])->save();
        $this->assertFalse($scope->meliputi($user->id, Peranan::Superadmin, $this->ptj(), $this->activity()));
        $user->forceFill(['is_active' => true, 'ptj_id' => null])->save();
        $this->assertFalse($scope->meliputi($user->id, Peranan::Superadmin, $this->ptj(), $this->activity()));
        $user->forceFill(['ptj_id' => $this->ptj()])->save();
        $assignment->delete();
        $this->assertFalse($scope->meliputi($user->id, Peranan::Superadmin, $this->ptj(), $this->activity()));
        $this->assertFalse($scope->meliputi(999999, Peranan::Superadmin, $this->ptj(), $this->activity()));
    }

    public function test_database_rejects_user_workplace_from_another_ptj(): void
    {
        $this->references();
        $foreign = TempatBertugas::where('ptj_id', '!=', $this->ptj())->firstOrFail();
        $this->expectException(QueryException::class);
        $this->person($this->ptj(), $foreign->id);
    }

    public function test_database_rejects_assignment_workplace_from_another_ptj(): void
    {
        $this->references();
        $user = $this->person();
        $foreign = TempatBertugas::where('ptj_id', '!=', $this->ptj())->firstOrFail();
        $this->expectException(QueryException::class);
        $this->assign($user, Peranan::PenyediaAnggaran, $this->ptj(), $this->activity(), $foreign->id);
    }

    #[DataProvider('invalidScopes')]
    public function test_database_rejects_invalid_role_scope_shape(string $role, bool $ptj, bool $activity, bool $workplace): void
    {
        $this->references();
        $user = $this->person();
        $place = TempatBertugas::where('ptj_id', $this->ptj())->firstOrFail();
        $this->expectException(QueryException::class);
        DB::table('penugasan_peranan')->insert([
            'user_id' => $user->id, 'peranan' => $role, 'ptj_id' => $ptj ? $this->ptj() : null,
            'aktiviti_kewangan_id' => $activity ? $this->activity() : null,
            'tempat_bertugas_id' => $workplace ? $place->id : null,
        ]);
    }

    public static function invalidScopes(): array
    {
        return [
            'unknown role' => ['invented_role', false, false, false],
            'scoped superadmin' => ['superadmin', true, true, false],
            'reviewer without activity' => ['ketua_bidaan', false, false, false],
            'reviewer with ptj restriction' => ['penyelia_bidaan', true, true, false],
            'preparer missing ptj' => ['penyedia_bidaan', false, true, false],
            'bid preparer with workplace scope' => ['penyedia_bidaan', true, true, true],
            'estimate preparer without activity' => ['penyedia_anggaran', true, false, false],
            'monitor combined scope' => ['pemantau_bidaan', true, true, false],
        ];
    }

    public function test_database_rejects_duplicate_global_assignment(): void
    {
        $this->references();
        $user = $this->person();
        $this->assign($user, Peranan::Superadmin);
        $this->expectException(QueryException::class);
        $this->assign($user, Peranan::Superadmin);
    }
}
