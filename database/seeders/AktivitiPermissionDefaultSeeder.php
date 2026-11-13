<?php

namespace Database\Seeders;

use App\Models\AktivitiKewangan;
use App\Models\TetapanAksesPtj;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AktivitiPermissionDefaultSeeder extends Seeder
{
    /** REF-05 bootstrap defaults only; do not overwrite later configuration. */
    public function run(): void
    {
        $defaults = [
            'Pengurusan Pentadbiran Negeri' => ['JPNT' => true, 'PPD' => true, 'SMK PTJ' => true],
            'Pendidikan Awal' => ['JPNT' => true, 'PPD' => true, 'SMK PTJ' => false],
            'Pendidikan Rendah' => ['JPNT' => true, 'PPD' => true, 'SMK PTJ' => false],
            'Pendidikan Menengah' => ['JPNT' => true, 'PPD' => true, 'SMK PTJ' => true],
            'Pengurusan Asrama' => ['JPNT' => true, 'PPD' => true, 'SMK PTJ' => true],
        ];
        DB::transaction(function () use ($defaults): void {
            $activities = AktivitiKewangan::whereIn('nama', array_keys($defaults))->pluck('id', 'nama');
            if ($activities->count() !== count($defaults)) {
                throw new RuntimeException('Missing approved Aktiviti Kewangan references; no defaults imported.');
            }
            foreach ($defaults as $name => $categories) {
                foreach ($categories as $category => $allowed) {
                    TetapanAksesPtj::firstOrCreate([
                        'kategori_ptj' => $category,
                        'ptj_id' => null,
                        'aktiviti_kewangan_id' => $activities[$name],
                        'rujukan_os_id' => null,
                    ], ['dibenarkan' => $allowed]);
                }
            }
        });
    }
}
