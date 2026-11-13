<?php

namespace Tests\Feature;

use App\Models\AktivitiKewangan;
use App\Models\Kitaran;
use App\Models\Ptj;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FoundationModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ptj_preserves_exact_codes_and_optional_district(): void
    {
        foreach (['4162B901', '00123456'] as $code) {
            $ptj = Ptj::create([
                'kod_ptj' => $code,
                'nama' => 'Nama PTJ',
                'kategori' => 'SMK PTJ',
            ])->fresh();

            $this->assertSame($code, $ptj->kod_ptj);
            $this->assertNull($ptj->daerah);
            $this->assertNotNull($ptj->created_at);
            $this->assertNotNull($ptj->updated_at);
        }

        $ptj->update(['daerah' => 'Kuala Terengganu']);

        $this->assertSame('Kuala Terengganu', $ptj->fresh()->daerah);
    }

    public function test_activity_has_a_canonical_name_and_optional_abbreviation(): void
    {
        $activity = AktivitiKewangan::create([
            'nama' => 'Pengurusan Pentadbiran Negeri',
        ])->fresh();

        $this->assertSame('Pengurusan Pentadbiran Negeri', $activity->nama);
        $this->assertNull($activity->singkatan);
        $this->assertNotNull($activity->created_at);
        $this->assertNotNull($activity->updated_at);

        $activity->update(['singkatan' => 'PPN']);

        $this->assertSame('PPN', $activity->fresh()->singkatan);
    }

    public function test_cycle_is_identified_only_by_its_year(): void
    {
        $cycle = Kitaran::create(['tahun' => 2028])->fresh();

        $this->assertSame(2028, $cycle->tahun);
        $this->assertNotNull($cycle->created_at);
        $this->assertNotNull($cycle->updated_at);

        Kitaran::create(['tahun' => 2029]);

        $this->assertDatabaseCount('kitarans', 2);
    }

    #[DataProvider('uniqueIdentities')]
    public function test_database_rejects_duplicate_identities(string $model, array $attributes): void
    {
        $model::create($attributes);

        $this->expectException(QueryException::class);

        $model::create($attributes);
    }

    public static function uniqueIdentities(): array
    {
        return [
            'PTJ code' => [Ptj::class, [
                'kod_ptj' => '4162B901',
                'nama' => 'SMK Tengku Bariah',
                'kategori' => 'SMK PTJ',
            ]],
            'canonical activity name' => [AktivitiKewangan::class, [
                'nama' => 'Pengurusan Pentadbiran Negeri',
            ]],
            'cycle year' => [Kitaran::class, ['tahun' => 2028]],
        ];
    }

    #[DataProvider('requiredFields')]
    public function test_database_rejects_null_required_fields(
        string $table,
        array $attributes,
        string $field,
    ): void {
        $attributes[$field] = null;

        $this->expectException(QueryException::class);

        // Bypass model casts to exercise the database NOT NULL constraint.
        $this->app['db']->table($table)->insert($attributes);
    }

    public static function requiredFields(): array
    {
        $ptj = ['kod_ptj' => '41620101', 'nama' => 'JPNT', 'kategori' => 'JPNT'];

        return [
            'PTJ code' => ['ptjs', $ptj, 'kod_ptj'],
            'PTJ name' => ['ptjs', $ptj, 'nama'],
            'PTJ category' => ['ptjs', $ptj, 'kategori'],
            'activity name' => ['aktiviti_kewangans', ['nama' => 'Pendidikan Awal'], 'nama'],
            'cycle year' => ['kitarans', ['tahun' => 2028], 'tahun'],
        ];
    }
}
