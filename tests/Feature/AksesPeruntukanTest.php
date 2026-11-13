<?php

namespace Tests\Feature;

use App\Models\AktivitiKewangan;
use App\Models\PemetaanOs;
use App\Models\Ptj;
use App\Models\RujukanOs;
use App\Models\TetapanAksesPtj;
use App\Services\AksesPeruntukan;
use Database\Seeders\AktivitiPermissionDefaultSeeder;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\OsReferenceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class AksesPeruntukanTest extends TestCase
{
    use RefreshDatabase;

    private function references(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->seed(OsReferenceSeeder::class);
    }

    private function activity(string $name = 'Pengurusan Pentadbiran Negeri'): int
    {
        return AktivitiKewangan::where('nama', $name)->firstOrFail()->id;
    }

    private function ptj(string $category = 'PPD'): int
    {
        return Ptj::where('kategori', $category)->orderBy('id')->firstOrFail()->id;
    }

    private function os(string $name = 'Bayaran Lebih Masa'): int
    {
        return RujukanOs::where('butiran_perbelanjaan', $name)->firstOrFail()->id;
    }

    private function setting(?string $category, ?int $ptj, int $activity, ?int $os, bool $allowed): TetapanAksesPtj
    {
        return TetapanAksesPtj::updateOrCreate([
            'kategori_ptj' => $category, 'ptj_id' => $ptj,
            'aktiviti_kewangan_id' => $activity, 'rujukan_os_id' => $os,
        ], ['dibenarkan' => $allowed]);
    }

    public function test_seeds_exactly_the_approved_activity_matrix_without_os_or_ptj_assignments(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        $this->assertDatabaseCount('tetapan_akses_ptj', 15);
        $this->assertSame(0, TetapanAksesPtj::whereNotNull('ptj_id')->count());
        $this->assertSame(0, TetapanAksesPtj::whereNotNull('rujukan_os_id')->count());
        foreach (['JPNT', 'PPD', 'SMK PTJ'] as $category) {
            foreach (AktivitiKewangan::all() as $activity) {
                $expected = ! ($category === 'SMK PTJ' && in_array($activity->nama, ['Pendidikan Awal', 'Pendidikan Rendah'], true));
                $this->assertSame($expected, $access->tetapanAktiviti($this->ptj($category), $activity->id));
            }
        }
        $this->assertNull($access->tetapanOs($this->ptj(), $this->activity(), $this->os()));
        $this->assertFalse($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
    }

    public function test_unconfigured_settings_remain_distinct_from_explicit_false_and_grant_nothing(): void
    {
        $this->references();
        $access = new AksesPeruntukan;
        $this->assertNull($access->tetapanAktiviti($this->ptj(), $this->activity()));
        $this->assertFalse($access->bolehAksesAktiviti($this->ptj(), $this->activity()));
        $this->setting('PPD', null, $this->activity(), null, false);
        $this->assertFalse($access->tetapanAktiviti($this->ptj(), $this->activity()));
        $this->assertNull($access->tetapanAktiviti(999999, $this->activity()));
        $this->assertNull($access->tetapanAktiviti($this->ptj(), 999999));
    }

    public function test_individual_enable_and_disable_override_only_the_selected_ptj_setting(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        $schools = Ptj::where('kategori', 'SMK PTJ')->orderBy('id')->take(2)->get();
        $early = $this->activity('Pendidikan Awal');
        $this->setting(null, $schools[0]->id, $early, null, true);
        $this->assertTrue($access->bolehAksesAktiviti($schools[0]->id, $early));
        $this->assertFalse($access->bolehAksesAktiviti($schools[1]->id, $early));
        $this->setting(null, $schools[0]->id, $this->activity(), null, false);
        $this->assertFalse($access->bolehAksesAktiviti($schools[0]->id, $this->activity()));
        $this->assertTrue($access->bolehAksesAktiviti($schools[1]->id, $this->activity()));
    }

    public function test_category_changes_follow_inheritance_and_reset_removes_only_the_override(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        $ppds = Ptj::where('kategori', 'PPD')->orderBy('id')->take(2)->get();
        $override = $this->setting(null, $ppds[0]->id, $this->activity(), null, true);
        $this->setting('PPD', null, $this->activity(), null, false);
        $this->assertTrue($access->bolehAksesAktiviti($ppds[0]->id, $this->activity()));
        $this->assertFalse($access->bolehAksesAktiviti($ppds[1]->id, $this->activity()));
        $override->delete();
        $this->assertFalse($access->bolehAksesAktiviti($ppds[0]->id, $this->activity()));
        $this->setting('PPD', null, $this->activity(), null, true);
        $this->assertTrue($access->bolehAksesAktiviti($ppds[0]->id, $this->activity()));
    }

    public function test_os_settings_are_separate_by_category_activity_and_os_variant(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        $this->setting('PPD', null, $this->activity(), $this->os(), true);
        $this->assertTrue($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
        $this->assertFalse($access->bolehAksesOs($this->ptj('SMK PTJ'), $this->activity(), $this->os()));
        $this->assertFalse($access->bolehAksesOs($this->ptj(), $this->activity('Pendidikan Menengah'), $this->os()));
        $sukan = $this->os('Perjalanan Sara Hidup (Sukan)');
        $kokurikulum = $this->os('Perjalanan Sara Hidup (Kokurikulum)');
        $this->setting('PPD', null, $this->activity(), $sukan, true);
        $this->assertTrue($access->bolehAksesOs($this->ptj(), $this->activity(), $sukan));
        $this->assertFalse($access->bolehAksesOs($this->ptj(), $this->activity(), $kokurikulum));
    }

    public function test_os_overrides_precede_category_defaults_and_can_revert_to_inheritance(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        $this->setting('PPD', null, $this->activity(), $this->os(), false);
        $override = $this->setting(null, $this->ptj(), $this->activity(), $this->os(), true);
        $this->assertTrue($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
        $this->setting('PPD', null, $this->activity(), $this->os(), true);
        $override->update(['dibenarkan' => false]);
        $this->assertFalse($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
        $override->delete();
        $this->assertTrue($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
    }

    public function test_disabling_activity_masks_os_without_erasing_configuration_or_references(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        $osSetting = $this->setting('PPD', null, $this->activity(), $this->os(), true);
        $this->assertTrue($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
        $this->setting('PPD', null, $this->activity(), null, false);
        $this->assertTrue($access->tetapanOs($this->ptj(), $this->activity(), $this->os()));
        $this->assertFalse($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
        $this->assertTrue($osSetting->fresh()->dibenarkan);
        $this->assertDatabaseCount('rujukan_os', 33);
        $this->assertDatabaseCount('pemetaan_os', 105);
        $this->setting('PPD', null, $this->activity(), null, true);
        $this->assertTrue($access->bolehAksesOs($this->ptj(), $this->activity(), $this->os()));
    }

    public function test_pol_and_amal_cannot_be_enabled_by_category_or_ptj_toggles(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        foreach ([
            ['Pendidikan Menengah', 'Pengajaran Bahasa Yang Diluluskan (POL)'],
            ['Pengurusan Asrama', 'Asrama Malaysia (AMal)'],
        ] as [$activityName, $osName]) {
            $activity = $this->activity($activityName);
            $os = $this->os($osName);
            $this->setting('PPD', null, $activity, $os, true);
            $this->setting(null, $this->ptj(), $activity, $os, true);
            $this->assertTrue($access->tetapanOs($this->ptj(), $activity, $os));
            $this->assertFalse($access->bolehAksesOs($this->ptj(), $activity, $os));
            $this->assertTrue(PemetaanOs::where('aktiviti_kewangan_id', $activity)->where('rujukan_os_id', $os)->exists());
        }
    }

    public function test_os_requires_a_source_mapping_for_the_selected_activity(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $access = new AksesPeruntukan;
        $taska = $this->os('Subsidi Yuran TASKA');
        $early = $this->activity('Pendidikan Awal');
        $this->setting('PPD', null, $early, $taska, true);
        $this->assertFalse($access->bolehAksesOs($this->ptj(), $early, $taska));
        $this->setting('PPD', null, $this->activity(), $taska, true);
        $this->assertTrue($access->bolehAksesOs($this->ptj(), $this->activity(), $taska));
    }

    public function test_repeated_default_seeding_preserves_custom_configuration_and_unrelated_rows(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $changed = $this->setting('PPD', null, $this->activity(), null, false);
        $override = $this->setting(null, $this->ptj(), $this->activity(), null, true);
        $osSetting = $this->setting('PPD', null, $this->activity(), $this->os(), true);
        $otherActivity = AktivitiKewangan::create(['nama' => 'Additional activity']);
        $other = $this->setting('PPD', null, $otherActivity->id, null, false);
        $ids = TetapanAksesPtj::orderBy('id')->pluck('id')->all();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $this->assertSame($ids, TetapanAksesPtj::orderBy('id')->pluck('id')->all());
        $this->assertFalse($changed->fresh()->dibenarkan);
        $this->assertTrue($override->fresh()->dibenarkan);
        $this->assertTrue($osSetting->fresh()->dibenarkan);
        $this->assertFalse($other->fresh()->dibenarkan);
    }

    public function test_missing_reference_aborts_default_import(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        // Use a missing named reference without deleting any parent record.
        AktivitiKewangan::where('nama', 'Pendidikan Awal')->update(['nama' => 'Unmatched name']);
        try {
            $this->seed(AktivitiPermissionDefaultSeeder::class);
            $this->fail('Missing reference must reject defaults.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Missing approved Aktiviti Kewangan', $exception->getMessage());
        }
        $this->assertDatabaseCount('tetapan_akses_ptj', 0);
    }

    #[DataProvider('invalidTargets')]
    public function test_database_rejects_invalid_configuration_target(?string $category, bool $includePtj): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->expectException(QueryException::class);
        $this->setting($category, $includePtj ? $this->ptj() : null, $this->activity(), null, true);
    }

    public static function invalidTargets(): array
    {
        return ['both targets' => ['PPD', true], 'no target' => [null, false], 'unknown category' => ['Sekolah', false]];
    }

    public function test_database_rejects_duplicate_category_activity_setting_with_null_columns(): void
    {
        $this->references();
        $this->seed(AktivitiPermissionDefaultSeeder::class);
        $existing = TetapanAksesPtj::firstOrFail();
        $this->expectException(QueryException::class);
        $existing->replicate()->save();
    }

    public function test_database_rejects_duplicate_individual_os_override(): void
    {
        $this->references();
        $existing = $this->setting(null, $this->ptj(), $this->activity(), $this->os(), true);
        $this->expectException(QueryException::class);
        $existing->replicate()->save();
    }

    public function test_database_rejects_unknown_parent_ptj(): void
    {
        $this->references();
        $this->expectException(QueryException::class);
        $this->setting(null, 999999, $this->activity(), null, true);
    }

    public function test_database_protects_os_referenced_by_configuration_from_deletion(): void
    {
        $this->references();
        $os = RujukanOs::create(['kod_objek' => '99999', 'butiran_perbelanjaan' => 'Additional reference']);
        $this->setting('PPD', null, $this->activity(), $os->id, true);
        $this->expectException(QueryException::class);
        $os->delete();
    }
}
