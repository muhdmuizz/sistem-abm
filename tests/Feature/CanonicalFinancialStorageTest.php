<?php

namespace Tests\Feature;

use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\ButiranAnggaran;
use App\Models\HasilPengiraan;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Models\RujukanOs;
use App\Models\TempatBertugas;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CanonicalFinancialStorageTest extends TestCase
{
    use RefreshDatabase;

    private function financialGraph(): array
    {
        $ptj = Ptj::create(['kod_ptj' => 'FIN-001', 'nama' => 'Financial fixture', 'kategori' => 'PPD']);
        $activity = AktivitiKewangan::create(['nama' => 'Financial activity fixture']);
        $cycle = Kitaran::create(['tahun' => 2028]);
        $workplace = TempatBertugas::create(['ptj_id' => $ptj->id, 'nama' => 'Sektor fixture', 'jenis' => 'sektor']);
        $os = RujukanOs::create(['kod_objek' => '14000', 'butiran_perbelanjaan' => 'OS fixture']);
        $bid = Bidaan::forceCreate(['ptj_id' => $ptj->id, 'aktiviti_kewangan_id' => $activity->id, 'kitaran_id' => $cycle->id])->fresh();
        $estimate = Anggaran::forceCreate(['bidaan_id' => $bid->id, 'ptj_id' => $ptj->id, 'rujukan_os_id' => $os->id])->fresh();
        return compact('ptj', 'activity', 'cycle', 'workplace', 'os', 'bid', 'estimate');
    }

    private function source(array $g, array $data = []): ButiranAnggaran
    {
        return ButiranAnggaran::forceCreate([
            'anggaran_id' => $g['estimate']->id, 'ptj_id' => $g['ptj']->id,
            'tempat_bertugas_id' => $g['workplace']->id, 'data_sumber' => (object) $data,
        ])->fresh();
    }

    private function output(ButiranAnggaran $source, string $amount, int $order = 1): HasilPengiraan
    {
        // Synthetic exact-number fixtures, not approved rates or OS calculations.
        return HasilPengiraan::forceCreate([
            'butiran_anggaran_id' => $source->id, 'semakan_sumber' => $source->semakan,
            'turutan' => $order, 'amaun' => $amount,
        ])->fresh();
    }

    private function rejectDatabaseWrite(callable $operation, string $state): void
    {
        try {
            DB::transaction($operation);
            $this->fail('Expected database integrity rejection.');
        } catch (QueryException $exception) {
            $this->assertSame($state, (string) $exception->getCode());
        }
    }

    public function test_financial_graph_keeps_real_parentage_and_defaults_inactive_without_creating_phases(): void
    {
        $g = $this->financialGraph();
        $source = $this->source($g);
        $output = $this->output($source, '0');

        $this->assertFalse($g['bid']->diaktifkan);
        $this->assertFalse($g['estimate']->diaktifkan);
        $this->assertSame(0, $g['bid']->semakan);
        $this->assertSame(0, $g['estimate']->semakan);
        $this->assertSame(0, $source->semakan);
        $this->assertEquals((object) [], $source->data_sumber);
        $this->assertEquals((object) [], $output->data_output);
        $this->assertTrue($g['bid']->ptj->is($g['ptj']));
        $this->assertTrue($g['bid']->aktivitiKewangan->is($g['activity']));
        $this->assertTrue($g['bid']->kitaran->is($g['cycle']));
        $this->assertTrue($g['estimate']->bidaan->is($g['bid']));
        $this->assertTrue($g['estimate']->rujukanOs->is($g['os']));
        $this->assertTrue($source->anggaran->is($g['estimate']));
        $this->assertTrue($source->tempatBertugas->is($g['workplace']));
        $this->assertTrue($output->butiranAnggaran->is($source));
        foreach ([$g['ptj']->bidaan, $g['activity']->bidaan, $g['cycle']->bidaan] as $bids) {
            $this->assertTrue($bids->sole()->is($g['bid']));
        }
        $this->assertTrue($g['bid']->anggaran->sole()->is($g['estimate']));
        $this->assertTrue($g['os']->anggaran->sole()->is($g['estimate']));
        $this->assertTrue($g['estimate']->butiran->sole()->is($source));
        $this->assertTrue($g['workplace']->butiranAnggaran->sole()->is($source));
        $this->assertTrue($source->hasilPengiraan->sole()->is($output));
        $this->assertDatabaseCount('fasa', 0);
    }

    public function test_bid_identity_is_ptj_activity_cycle_and_does_not_depend_on_phase(): void
    {
        $g = $this->financialGraph();
        $this->rejectDatabaseWrite(fn () => DB::table('bidaan')->insert([
            'ptj_id' => $g['ptj']->id, 'aktiviti_kewangan_id' => $g['activity']->id, 'kitaran_id' => $g['cycle']->id,
        ]), '23505');
        $cycle = Kitaran::create(['tahun' => 2029]);
        $activity = AktivitiKewangan::create(['nama' => 'Another activity']);
        $ptj = Ptj::create(['kod_ptj' => 'FIN-002', 'nama' => 'Another PTJ', 'kategori' => 'PPD']);
        foreach ([
            ['kitaran_id' => $cycle->id],
            ['aktiviti_kewangan_id' => $activity->id],
            ['ptj_id' => $ptj->id],
        ] as $changed) {
            Bidaan::forceCreate(array_replace([
                'ptj_id' => $g['ptj']->id, 'aktiviti_kewangan_id' => $g['activity']->id, 'kitaran_id' => $g['cycle']->id,
            ], $changed));
        }
        $this->assertDatabaseCount('bidaan', 4);
    }

    public function test_one_catalogue_os_per_bid_preserves_variants_sharing_a_code(): void
    {
        $g = $this->financialGraph();
        $this->rejectDatabaseWrite(fn () => Anggaran::forceCreate([
            'bidaan_id' => $g['bid']->id, 'ptj_id' => $g['ptj']->id, 'rujukan_os_id' => $g['os']->id,
        ]), '23505');
        $variant = RujukanOs::create(['kod_objek' => '14000', 'butiran_perbelanjaan' => 'Distinct variant']);
        Anggaran::forceCreate(['bidaan_id' => $g['bid']->id, 'ptj_id' => $g['ptj']->id, 'rujukan_os_id' => $variant->id]);
        $this->assertDatabaseCount('anggaran', 2);
    }

    public function test_sources_with_identical_values_keep_separate_ids_and_independent_edits(): void
    {
        $g = $this->financialGraph();
        $first = $this->source($g, ['fixture_text' => 'same']);
        $second = $this->source($g, ['fixture_text' => 'same']);
        $id = $first->id;
        $first->forceFill(['data_sumber' => (object) ['fixture_text' => 'edited'], 'semakan' => 1])->save();

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame($id, $first->fresh()->id);
        $this->assertSame('edited', $first->fresh()->data_sumber->fixture_text);
        $this->assertSame('same', $second->fresh()->data_sumber->fixture_text);
        $this->assertSame(0, $second->fresh()->semakan);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
    }

    public function test_each_source_requires_a_specific_workplace_under_the_same_financial_ptj(): void
    {
        $g = $this->financialGraph();
        $other = Ptj::create(['kod_ptj' => 'FIN-OTHER', 'nama' => 'Other', 'kategori' => 'PPD']);
        $foreignWorkplace = TempatBertugas::create(['ptj_id' => $other->id, 'nama' => 'Foreign sector', 'jenis' => 'sektor']);
        $base = ['anggaran_id' => $g['estimate']->id, 'ptj_id' => $g['ptj']->id, 'tempat_bertugas_id' => $foreignWorkplace->id];
        $this->rejectDatabaseWrite(fn () => DB::table('butiran_anggaran')->insert($base), '23503');
        $this->rejectDatabaseWrite(fn () => DB::table('butiran_anggaran')->insert(array_replace($base, ['ptj_id' => $other->id])), '23503');
        $this->rejectDatabaseWrite(fn () => DB::table('butiran_anggaran')->insert(array_replace($base, ['tempat_bertugas_id' => null])), '23502');
        $this->rejectDatabaseWrite(fn () => DB::table('tempat_bertugas')->insert([
            'ptj_id' => $g['ptj']->id, 'nama' => 'Generic PPD', 'jenis' => 'ppd',
        ]), '23514');
        $this->assertDatabaseCount('butiran_anggaran', 0);
    }

    public function test_an_estimate_cannot_claim_a_ptj_different_from_its_bid(): void
    {
        $g = $this->financialGraph();
        $other = Ptj::create(['kod_ptj' => 'FIN-OTHER', 'nama' => 'Other', 'kategori' => 'PPD']);
        $this->rejectDatabaseWrite(fn () => DB::table('anggaran')->where('id', $g['estimate']->id)->update(['ptj_id' => $other->id]), '23503');
        $this->assertSame($g['ptj']->id, $g['estimate']->fresh()->ptj_id);
    }

    public function test_source_can_have_many_exactly_attributable_outputs_with_independent_ids(): void
    {
        $g = $this->financialGraph();
        $a = $this->source($g);
        $b = $this->source($g);
        $one = $this->output($a, '1.01');
        $two = $this->output($a, '2.02', 2);
        $other = $this->output($b, '3.03');
        $two->forceFill(['amaun' => '2.020001', 'data_output' => (object) ['fixture' => 'revised']])->save();

        $this->assertCount(2, $a->hasilPengiraan);
        $this->assertNotSame($one->id, $two->id);
        $this->assertSame('1.01', $one->fresh()->amaun);
        $this->assertSame('3.03', $other->fresh()->amaun);
        $this->assertSame('2.020001', $two->fresh()->amaun);
        $this->assertSame($a->id, $two->fresh()->butiran_anggaran_id);
        $this->rejectDatabaseWrite(fn () => $this->output($a, '4', 1), '23505');
        $this->rejectDatabaseWrite(fn () => $this->output($a, '4', 0), '23514');
    }

    public function test_generated_outputs_cannot_reference_a_missing_or_different_source_revision(): void
    {
        $g = $this->financialGraph();
        $source = $this->source($g);
        $base = ['butiran_anggaran_id' => $source->id, 'semakan_sumber' => 1, 'turutan' => 1, 'amaun' => '1'];
        $this->rejectDatabaseWrite(fn () => DB::table('hasil_pengiraan')->insert($base), '23503');
        $output = $this->output($source, '1');
        $this->rejectDatabaseWrite(fn () => DB::table('butiran_anggaran')->where('id', $source->id)->update(['semakan' => 1]), '23503');
        $this->assertSame(0, $source->fresh()->semakan);
        $this->assertSame(0, $output->fresh()->semakan_sumber);
        // Future audited recalculation replaces only affected editable outputs in a transaction.
        DB::transaction(function () use ($source, $output): void {
            $output->delete();
            $source->forceFill(['semakan' => 1])->save();
            $this->output($source, '1.25');
        });
        $this->assertSame(1, $source->fresh()->hasilPengiraan->sole()->semakan_sumber);
    }

    public function test_money_round_trips_exactly_without_float_scale_caps_or_positive_only_policy(): void
    {
        $g = $this->financialGraph();
        $source = $this->source($g);
        foreach ([
            '0', '-0.000001', '0.123456789012345678901234567890',
            '999999999999999999999999999999999999999999.123456789',
        ] as $i => $amount) {
            $this->assertSame($amount, $this->output($source, $amount, $i + 1)->amaun);
        }
        $sum = DB::table('hasil_pengiraan')->where('butiran_anggaran_id', $source->id)->selectRaw('sum(amaun)::text as total')->first()->total;
        $this->assertSame('999999999999999999999999999999999999999999.246912578012345678901234567890', $sum);
    }

    #[DataProvider('nonFiniteAmounts')]
    public function test_database_rejects_non_finite_financial_amounts(string $amount): void
    {
        $source = $this->source($this->financialGraph());
        $this->rejectDatabaseWrite(fn () => $this->output($source, $amount), '23514');
        $this->assertDatabaseCount('hasil_pengiraan', 0);
    }

    public static function nonFiniteAmounts(): array
    {
        return [['NaN'], ['Infinity'], ['-Infinity']];
    }

    public function test_financial_parents_and_workplace_attribution_cannot_be_destructively_cascaded(): void
    {
        $g = $this->financialGraph();
        $source = $this->source($g);
        $this->output($source, '1');
        foreach ([
            ['ptjs', $g['ptj']->id], ['aktiviti_kewangans', $g['activity']->id],
            ['kitarans', $g['cycle']->id], ['rujukan_os', $g['os']->id],
            ['bidaan', $g['bid']->id], ['anggaran', $g['estimate']->id],
            ['butiran_anggaran', $source->id], ['tempat_bertugas', $g['workplace']->id],
        ] as [$table, $id]) {
            $this->rejectDatabaseWrite(fn () => DB::table($table)->where('id', $id)->delete(), '23503');
        }
        $this->assertDatabaseCount('bidaan', 1);
        $this->assertDatabaseCount('anggaran', 1);
        $this->assertDatabaseCount('butiran_anggaran', 1);
        $this->assertDatabaseCount('hasil_pengiraan', 1);
    }

    public function test_missing_parents_nulls_and_negative_technical_revisions_are_rejected(): void
    {
        $g = $this->financialGraph();
        $source = $this->source($g);
        foreach ([
            ['bidaan', $g['bid']->id, 'ptj_id'],
            ['bidaan', $g['bid']->id, 'aktiviti_kewangan_id'],
            ['bidaan', $g['bid']->id, 'kitaran_id'],
            ['anggaran', $g['estimate']->id, 'bidaan_id'],
            ['anggaran', $g['estimate']->id, 'rujukan_os_id'],
            ['butiran_anggaran', $source->id, 'anggaran_id'],
            ['butiran_anggaran', $source->id, 'tempat_bertugas_id'],
        ] as [$table, $id, $column]) {
            $this->rejectDatabaseWrite(fn () => DB::table($table)->where('id', $id)->update([$column => 999999999]), '23503');
            $this->rejectDatabaseWrite(fn () => DB::table($table)->where('id', $id)->update([$column => null]), '23502');
        }
        foreach ([
            ['bidaan', $g['bid']->id], ['anggaran', $g['estimate']->id], ['butiran_anggaran', $source->id],
        ] as [$table, $id]) {
            $this->rejectDatabaseWrite(fn () => DB::table($table)->where('id', $id)->update(['semakan' => -1]), '23514');
        }
        $this->rejectDatabaseWrite(fn () => DB::table('hasil_pengiraan')->insert([
            'butiran_anggaran_id' => 999999999, 'semakan_sumber' => 0, 'turutan' => 1, 'amaun' => '1',
        ]), '23503');
    }

    public function test_draft_envelopes_accept_partial_objects_but_reject_arrays_scalars_and_nulls(): void
    {
        $g = $this->financialGraph();
        $source = $this->source($g, ['fixture_optional' => null, 'fixture_decimal_text' => '0.000000001']);
        $output = $this->output($source, '0');
        $this->assertNull($source->data_sumber->fixture_optional);
        $this->assertSame('0.000000001', $source->data_sumber->fixture_decimal_text);
        foreach ([
            ['butiran_anggaran', $source->id, 'data_sumber'],
            ['hasil_pengiraan', $output->id, 'data_output'],
        ] as [$table, $id, $column]) {
            foreach (['[]', '"text"', '0', 'null'] as $invalid) {
                $this->rejectDatabaseWrite(fn () => DB::table($table)->where('id', $id)->update([$column => $invalid]), '23514');
            }
            $this->rejectDatabaseWrite(fn () => DB::table($table)->where('id', $id)->update([$column => null]), '23502');
        }
    }

    public function test_failed_multi_row_storage_transaction_leaves_no_partial_sources_or_outputs(): void
    {
        $g = $this->financialGraph();
        $this->rejectDatabaseWrite(function () use ($g): void {
            $first = $this->source($g, ['fixture_text' => 'first']);
            $this->output($first, '1');
            $second = $this->source($g, ['fixture_text' => 'second']);
            $this->output($second, 'NaN');
        }, '23514');
        $this->assertDatabaseCount('butiran_anggaran', 0);
        $this->assertDatabaseCount('hasil_pengiraan', 0);
        $this->assertDatabaseCount('anggaran', 1);
    }

    public function test_more_than_one_thousand_independent_sources_can_be_paged_without_merging(): void
    {
        $g = $this->financialGraph();
        $rows = array_fill(0, 1001, [
            'anggaran_id' => $g['estimate']->id, 'ptj_id' => $g['ptj']->id,
            'tempat_bertugas_id' => $g['workplace']->id, 'data_sumber' => '{"fixture_text":"same"}',
        ]);
        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('butiran_anggaran')->insert($chunk);
        }
        $page = $g['estimate']->butiran()->orderBy('id')->simplePaginate(100);
        $this->assertCount(100, $page->items());
        $this->assertTrue($page->hasMorePages());
        $this->assertSame(1001, $g['estimate']->butiran()->count());
        $this->assertSame(1001, $g['estimate']->butiran()->distinct()->count('id'));
        $this->assertDatabaseCount('hasil_pengiraan', 0);
    }

    public function test_request_payload_cannot_mass_assign_financial_identity_activation_or_amount(): void
    {
        $g = $this->financialGraph();
        foreach ([
            [new Bidaan, ['ptj_id' => $g['ptj']->id, 'diaktifkan' => true]],
            [new Anggaran, ['bidaan_id' => $g['bid']->id, 'diaktifkan' => true]],
            [new ButiranAnggaran, ['anggaran_id' => $g['estimate']->id, 'data_sumber' => (object) []]],
            [new HasilPengiraan, ['butiran_anggaran_id' => 1, 'amaun' => '999']],
        ] as [$model, $payload]) {
            try {
                $model->fill($payload);
                $this->fail('Financial models must require explicit trusted writes.');
            } catch (MassAssignmentException) {
                $this->assertFalse($model->isDirty());
            }
        }
    }

    public function test_storage_migration_is_reversible_and_preserves_organisational_references(): void
    {
        $g = $this->financialGraph();
        $this->output($this->source($g), '1');
        $migration = require database_path('migrations/2026_11_14_020000_create_canonical_financial_storage.php');
        $migration->down();
        $this->assertDatabaseCount('ptjs', 1);
        $this->assertDatabaseCount('tempat_bertugas', 1);
        $this->assertDatabaseCount('rujukan_os', 1);
        $migration->up();
        foreach (['bidaan', 'anggaran', 'butiran_anggaran', 'hasil_pengiraan'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertTrue(TempatBertugas::findOrFail($g['workplace']->id)->ptj->is($g['ptj']));
    }
}
