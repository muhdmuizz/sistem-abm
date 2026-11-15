<?php

namespace Tests\Feature;

use App\Models\AktivitiKewangan;
use App\Models\KodAktiviti;
use App\Models\PemetaanOs;
use App\Models\RujukanOs;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\OsReferenceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OsReferenceSeederTest extends TestCase
{
    use RefreshDatabase;

    private function importReferences(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->seed(OsReferenceSeeder::class);
    }

    public function test_imports_the_committed_catalogue_and_mappings_without_financial_records(): void
    {
        $this->importReferences();
        $this->assertDatabaseCount('rujukan_os', 32);
        $this->assertDatabaseCount('pemetaan_os', 105);
        $this->assertDatabaseCount('kod_aktivitis', 38);
        $this->assertDatabaseCount('aktiviti_kewangans', 5);
        $this->assertSame(30, RujukanOs::untukPelaksanaan()->count());
        $this->assertSame(103, PemetaanOs::untukPelaksanaan()->count());

        foreach (PemetaanOs::with(['kodAktiviti', 'aktivitiKewangan', 'rujukanOs'])->get() as $mapping) {
            $this->assertNotNull($mapping->rujukanOs);
            $this->assertNotNull($mapping->aktivitiKewangan);
            if ($mapping->kod_aktiviti_id !== null) {
                $this->assertSame($mapping->aktiviti_kewangan_id, $mapping->kodAktiviti->aktiviti_kewangan_id);
            }
        }
    }

    public function test_same_object_code_retains_distinct_os_and_shared_sukan_kokurikulum_mapping(): void
    {
        $this->importReferences();
        $this->assertSame(4, RujukanOs::where('kod_objek', '21000')->count());
        $code = KodAktiviti::where('kod_aktiviti', '010803')->firstOrFail();
        $mappings = PemetaanOs::where('kod_aktiviti_id', $code->id)->with('rujukanOs')->get();
        $this->assertCount(2, $mappings);
        $this->assertEqualsCanonicalizing([
            'Perjalanan Sara Hidup (Sukan)', 'Perjalanan Sara Hidup (Kokurikulum)',
        ], $mappings->map(fn ($mapping) => $mapping->rujukanOs->butiran_perbelanjaan)->all());
        $this->assertSame('010803', $code->kod_aktiviti);
    }

    public function test_exclusions_remain_reference_only(): void
    {
        $this->importReferences();
        foreach (['030306' => 'Pengajaran Bahasa Yang Diluluskan (POL)', '030318' => 'Asrama Malaysia (AMal)'] as $code => $name) {
            $reference = RujukanOs::where('butiran_perbelanjaan', $name)->firstOrFail();
            $this->assertTrue($reference->dikecualikan);
            $mapping = $reference->pemetaan()->sole();
            $this->assertSame((string) $code, $mapping->kodAktiviti->kod_aktiviti);
            $this->assertFalse(RujukanOs::untukPelaksanaan()->whereKey($reference->id)->exists());
            $this->assertFalse(PemetaanOs::untukPelaksanaan()->whereKey($mapping->id)->exists());
        }
    }

    public function test_taska_uses_the_owner_confirmed_code_and_ppn_parent(): void
    {
        $this->importReferences();
        $taska = RujukanOs::where('butiran_perbelanjaan', 'Subsidi Yuran TASKA')->firstOrFail();
        $this->assertSame('42000', $taska->kod_objek);
        $mapping = $taska->pemetaan()->sole();
        $this->assertSame('010102', $mapping->kodAktiviti->kod_aktiviti);
        $this->assertSame($mapping->aktiviti_kewangan_id, $mapping->kodAktiviti->aktiviti_kewangan_id);
        $this->assertSame('Pengurusan Pentadbiran Negeri', $mapping->aktivitiKewangan->nama);
        $this->assertDatabaseCount('kod_aktivitis', 38);
    }

    public function test_existing_blank_taska_mapping_is_upgraded_in_place(): void
    {
        $this->importReferences();
        $taska = RujukanOs::where('butiran_perbelanjaan', 'Subsidi Yuran TASKA')->firstOrFail();
        $mapping = $taska->pemetaan()->sole();
        $mapping->update(['kod_aktiviti_id' => null]);
        $originalId = $mapping->id;
        $this->seed(OsReferenceSeeder::class);
        $this->seed(OsReferenceSeeder::class);
        $updated = $taska->pemetaan()->sole();
        $this->assertSame($originalId, $updated->id);
        $this->assertSame('010102', $updated->kodAktiviti->kod_aktiviti);
        $this->assertSame('Pengurusan Pentadbiran Negeri', $updated->aktivitiKewangan->nama);
        $this->assertDatabaseCount('pemetaan_os', 105);
        $this->assertDatabaseCount('kod_aktivitis', 38);
    }

    public function test_conflicting_taska_mappings_are_not_silently_deleted(): void
    {
        $this->importReferences();
        $taska = RujukanOs::where('butiran_perbelanjaan', 'Subsidi Yuran TASKA')->firstOrFail();
        $current = $taska->pemetaan()->sole();
        $legacy = $current->replicate();
        $legacy->kod_aktiviti_id = null;
        $legacy->save();
        try {
            $this->seed(OsReferenceSeeder::class);
            $this->fail('Conflicting mappings must require reconciliation.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Conflicting TASKA mappings', $exception->getMessage());
        }
        $this->assertSame(2, $taska->pemetaan()->count());
        $this->assertNull($legacy->fresh()->kod_aktiviti_id);
        $this->assertSame($current->kod_aktiviti_id, $current->fresh()->kod_aktiviti_id);
    }

    public function test_original_wording_and_deduplicated_mapping_are_preserved(): void
    {
        $this->importReferences();
        $mapping = PemetaanOs::where('butiran_asal', 'Gajl dan Upahan')->firstOrFail();
        $this->assertSame('Gaji dan Upahan', $mapping->rujukanOs->butiran_perbelanjaan);
        $code = KodAktiviti::where('kod_aktiviti', '010801')->firstOrFail();
        $this->assertSame(1, PemetaanOs::where('kod_aktiviti_id', $code->id)
            ->whereHas('rujukanOs', fn ($query) => $query->where('kod_objek', '28000'))->count());
        $kokurikulum = RujukanOs::where('butiran_perbelanjaan', 'Perjalanan Sara Hidup (Kokurikulum)')->firstOrFail();
        $this->assertSame('', $kokurikulum->pemetaan()->sole()->butiran_asal);
    }

    public function test_repeat_import_preserves_ids_and_unrelated_records(): void
    {
        $this->importReferences();
        $referenceIds = RujukanOs::orderBy('id')->pluck('id')->all();
        $mappingIds = PemetaanOs::orderBy('id')->pluck('id')->all();
        $custom = RujukanOs::create(['kod_objek' => '99999', 'butiran_perbelanjaan' => 'Custom reference']);
        $mapping = PemetaanOs::create([
            'aktiviti_kewangan_id' => AktivitiKewangan::firstOrFail()->id,
            'kod_aktiviti_id' => null, 'rujukan_os_id' => $custom->id,
            'singkatan_sumber' => 'Custom', 'butiran_asal' => 'Custom source', 'status_sumber' => 'Custom',
        ]);
        $this->seed(OsReferenceSeeder::class);
        $this->assertDatabaseCount('rujukan_os', 33);
        $this->assertDatabaseCount('pemetaan_os', 106);
        $this->assertSame($referenceIds, RujukanOs::whereKeyNot($custom->id)->orderBy('id')->pluck('id')->all());
        $this->assertSame($mappingIds, PemetaanOs::whereKeyNot($mapping->id)->orderBy('id')->pluck('id')->all());
        $this->assertSame('Custom source', $mapping->fresh()->butiran_asal);
    }

    public function test_missing_code_aborts_import_before_either_table_is_written(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        KodAktiviti::where('kod_aktiviti', '010801')->delete();
        try {
            $this->seed(OsReferenceSeeder::class);
            $this->fail('Missing code must reject the import.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Missing or mismatched Kod Aktiviti', $exception->getMessage());
        }
        $this->assertDatabaseCount('rujukan_os', 0);
        $this->assertDatabaseCount('pemetaan_os', 0);
    }

    public function test_wrong_code_parent_aborts_import(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        KodAktiviti::where('kod_aktiviti', '010801')->update([
            'aktiviti_kewangan_id' => AktivitiKewangan::where('nama', 'Pendidikan Awal')->firstOrFail()->id,
        ]);
        try {
            $this->seed(OsReferenceSeeder::class);
            $this->fail('Wrong code parent must reject the import.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Missing or mismatched Kod Aktiviti', $exception->getMessage());
        }
        $this->assertDatabaseCount('rujukan_os', 0);
        $this->assertDatabaseCount('pemetaan_os', 0);
    }

    public function test_database_rejects_duplicate_os_identity(): void
    {
        $this->importReferences();
        $this->expectException(QueryException::class);
        RujukanOs::create(RujukanOs::firstOrFail()->only(['kod_objek', 'butiran_perbelanjaan']));
    }

    public function test_database_rejects_duplicate_blank_code_mapping(): void
    {
        $this->importReferences();
        $mapping = PemetaanOs::whereHas('rujukanOs', fn ($query) => $query->where('butiran_perbelanjaan', 'Subsidi Yuran TASKA'))->sole();
        $mapping->update(['kod_aktiviti_id' => null]);
        $this->expectException(QueryException::class);
        PemetaanOs::create($mapping->only([
            'aktiviti_kewangan_id', 'kod_aktiviti_id', 'rujukan_os_id',
            'singkatan_sumber', 'butiran_asal', 'status_sumber',
        ]));
    }

    public function test_database_rejects_mapping_to_a_code_from_another_activity(): void
    {
        $this->importReferences();
        $mapping = PemetaanOs::whereNotNull('kod_aktiviti_id')->firstOrFail();
        $this->expectException(QueryException::class);
        $mapping->update(['aktiviti_kewangan_id' => AktivitiKewangan::whereKeyNot($mapping->aktiviti_kewangan_id)->firstOrFail()->id]);
    }

    public function test_database_protects_mapped_os_from_deletion(): void
    {
        $this->importReferences();
        $this->expectException(QueryException::class);
        RujukanOs::firstOrFail()->delete();
    }

    public function test_database_protects_mapped_code_from_deletion(): void
    {
        $this->importReferences();
        $this->expectException(QueryException::class);
        KodAktiviti::where('kod_aktiviti', '010801')->firstOrFail()->delete();
    }

    public function test_database_rejects_non_numeric_object_code(): void
    {
        $this->expectException(QueryException::class);
        RujukanOs::create(['kod_objek' => 'AB123', 'butiran_perbelanjaan' => 'Invalid code']);
    }
}
