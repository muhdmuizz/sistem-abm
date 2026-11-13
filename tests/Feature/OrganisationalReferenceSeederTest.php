<?php

namespace Tests\Feature;

use App\Models\AktivitiKewangan;
use App\Models\KodAktiviti;
use App\Models\Ptj;
use Database\Seeders\OrganisationalReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationalReferenceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_only_the_approved_reference_records(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);

        $this->assertDatabaseCount('ptjs', 69);
        $this->assertDatabaseCount('aktiviti_kewangans', 5);
        $this->assertDatabaseCount('kod_aktivitis', 38);

        $this->assertSame(1, Ptj::where('kategori', 'JPNT')->count());
        $this->assertSame(8, Ptj::where('kategori', 'PPD')->count());
        $this->assertSame(60, Ptj::where('kategori', 'SMK PTJ')->count());

        $this->assertDatabaseHas('ptjs', [
            'kod_ptj' => '41621601', 'daerah' => 'Kuala Nerus',
        ]);
        $this->assertDatabaseHas('ptjs', [
            'kod_ptj' => '4162C101', 'daerah' => 'Kuala Nerus',
        ]);

        $ppn = AktivitiKewangan::where('nama', 'Pengurusan Pentadbiran Negeri')->firstOrFail();
        $this->assertSame('PPN', $ppn->singkatan);
        $this->assertSame(10, KodAktiviti::where('aktiviti_kewangan_id', $ppn->id)->count());

        $this->assertSame(
            'Pengurusan Pentadbiran Negeri',
            KodAktiviti::where('kod_aktiviti', '010801')->firstOrFail()
                ->aktivitiKewangan->nama,
        );
        $this->assertDatabaseHas('kod_aktivitis', ['kod_aktiviti' => '030306']);
        $this->assertDatabaseHas('kod_aktivitis', ['kod_aktiviti' => '030318']);
    }

    public function test_reference_import_is_idempotent_and_preserves_existing_other_rows(): void
    {
        Ptj::create([
            'kod_ptj' => '99999999',
            'nama' => 'Additional PTJ',
            'kategori' => 'PPD',
        ]);

        $this->seed(OrganisationalReferenceSeeder::class);
        $this->seed(OrganisationalReferenceSeeder::class);

        $this->assertDatabaseCount('ptjs', 70);
        $this->assertDatabaseCount('aktiviti_kewangans', 5);
        $this->assertDatabaseCount('kod_aktivitis', 38);
        $this->assertDatabaseHas('ptjs', [
            'kod_ptj' => '99999999', 'nama' => 'Additional PTJ',
        ]);
    }

    public function test_kod_aktiviti_identity_is_unique_and_preserves_leading_zeroes(): void
    {
        $this->seed(OrganisationalReferenceSeeder::class);

        $code = KodAktiviti::where('kod_aktiviti', '010801')->firstOrFail();
        $this->assertSame('010801', $code->kod_aktiviti);
        $this->assertSame(6, strlen($code->kod_aktiviti));
    }
}
