<?php

namespace Tests\Feature;

use App\Models\Ptj;
use App\Models\TempatBertugas;
use Database\Seeders\OrganisationalReferenceSeeder;
use Database\Seeders\TempatBertugasReferenceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TempatBertugasReferenceTest extends TestCase
{
    use RefreshDatabase;

    private function importReferences(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->seed(TempatBertugasReferenceSeeder::class);
    }

    public function test_imports_workplaces_under_the_correct_ptj_context(): void
    {
        $this->importReferences();
        $this->assertDatabaseCount('ptjs', 69);
        $this->assertDatabaseCount('tempat_bertugas', 579);
        $this->assertSame(71, TempatBertugas::sektor()->count());
        $jpnt = Ptj::where('kod_ptj', '41620101')->firstOrFail();
        $this->assertSame(15, $jpnt->tempatBertugas()->sektor()->count());
        $this->assertTrue($jpnt->tempatBertugas()->where('nama', 'Pusat Kokurikulum Negeri')->exists());
        $this->assertFalse(TempatBertugas::where('nama', 'Pusat Kokurikulum Daerah')->exists());

        $counts = ['Besut' => 67, 'Dungun' => 62, 'Kemaman' => 63,
            'Kuala Terengganu' => 64, 'Marang' => 36, 'Hulu Terengganu' => 62,
            'Setiu' => 53, 'Kuala Nerus' => 41];
        foreach ($counts as $district => $count) {
            $ppd = Ptj::where('kategori', 'PPD')->where('daerah', $district)->firstOrFail();
            $this->assertSame(7, $ppd->tempatBertugas()->sektor()->count());
            $this->assertSame($count, $ppd->tempatBertugas()->where('jenis', 'sekolah_non_ptj')->count());
            $this->assertFalse(TempatBertugas::where('nama', $ppd->nama)->exists());
        }
        $this->assertFalse(TempatBertugas::where('nama', 'JPNT')->exists());
        foreach (Ptj::where('kategori', 'SMK PTJ')->get() as $ptj) {
            $workplace = $ptj->tempatBertugas()->sole();
            $this->assertSame('smk_ptj', $workplace->jenis);
            $this->assertSame($ptj->nama, $workplace->nama);
            $this->assertTrue($workplace->ptj->is($ptj));
        }
        foreach (['41621601', '4162C101'] as $code) {
            $ptj = Ptj::where('kod_ptj', $code)->firstOrFail();
            $this->assertSame('Kuala Nerus', $ptj->daerah);
            $this->assertFalse(TempatBertugas::where('jenis', 'sekolah_non_ptj')->where('nama', $ptj->nama)->exists());
        }
    }

    public function test_rerun_keeps_ids_and_unrelated_workplaces(): void
    {
        $this->importReferences();
        $ids = TempatBertugas::orderBy('id')->pluck('id')->all();
        $custom = TempatBertugas::create([
            'ptj_id' => Ptj::where('kategori', 'PPD')->firstOrFail()->id,
            'nama' => 'Additional workplace', 'jenis' => 'sektor',
        ]);
        $this->seed(TempatBertugasReferenceSeeder::class);
        $this->assertDatabaseCount('tempat_bertugas', 580);
        $this->assertSame($ids, TempatBertugas::where('id', '!=', $custom->id)->orderBy('id')->pluck('id')->all());
        $this->assertTrue($custom->fresh()->is($custom));
    }

    public function test_missing_parent_does_not_partially_import_workplaces(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        Ptj::where('kod_ptj', '41621001')->delete();
        try {
            $this->seed(TempatBertugasReferenceSeeder::class);
            $this->fail('Missing parent should reject the import.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Missing or inconsistent PTJ', $exception->getMessage());
        }
        $this->assertDatabaseCount('tempat_bertugas', 0);
    }

    public function test_same_named_sectors_in_different_ppds_are_distinct(): void
    {
        $this->importReferences();
        $sectors = TempatBertugas::sektor()->where('nama', 'Sektor Perancangan')->get();
        $this->assertCount(8, $sectors);
        $this->assertCount(8, $sectors->pluck('ptj_id')->unique());
    }

    public function test_database_rejects_duplicate_workplace_identity(): void
    {
        $this->importReferences();
        $record = TempatBertugas::firstOrFail();
        $this->expectException(QueryException::class);
        TempatBertugas::create($record->only(['ptj_id', 'jenis', 'nama']));
    }

    public function test_database_rejects_unknown_parent(): void
    {
        $this->expectException(QueryException::class);
        TempatBertugas::create(['ptj_id' => 999999, 'jenis' => 'sektor', 'nama' => 'Invalid parent']);
    }

    public function test_database_protects_parent_with_workplaces_from_deletion(): void
    {
        $this->importReferences();
        $this->expectException(QueryException::class);
        TempatBertugas::firstOrFail()->ptj->delete();
    }

    public function test_database_rejects_generic_parent_workplace_type(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->expectException(QueryException::class);
        TempatBertugas::create([
            'ptj_id' => Ptj::where('kategori', 'PPD')->firstOrFail()->id,
            'jenis' => 'ppd', 'nama' => 'Generic PPD',
        ]);
    }

    public function test_database_rejects_blank_workplace_name(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);
        $this->expectException(QueryException::class);
        TempatBertugas::create([
            'ptj_id' => Ptj::where('kategori', 'PPD')->firstOrFail()->id,
            'jenis' => 'sektor', 'nama' => '   ',
        ]);
    }
}
