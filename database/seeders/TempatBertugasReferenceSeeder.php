<?php

namespace Database\Seeders;

use App\Models\Ptj;
use App\Models\TempatBertugas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TempatBertugasReferenceSeeder extends Seeder
{
    /** Requires Batch 002 references; never deletes records or grants access. */
    public function run(): void
    {
        $sectors = $this->readCsv('sektor-master.csv', ['Kod PTJ Induk', 'PTJ', 'Sektor']);
        $schools = $this->readCsv('non-ptj-schools.csv', ['PPD', 'Tempat Bertugas']);
        $ptjs = $this->readCsv('ptj-master.csv', ['Kod PTJ', 'PTJ', 'Kategori', 'Daerah']);

        DB::transaction(function () use ($sectors, $schools, $ptjs): void {
            $parents = [];
            $ppds = [];
            $schoolPtjs = [];
            foreach ($ptjs as $row) {
                $parent = Ptj::where('kod_ptj', $row['Kod PTJ'])->first();
                if ($parent === null || $parent->nama !== $row['PTJ'] ||
                    $parent->kategori !== $row['Kategori']) {
                    throw new RuntimeException('Missing or inconsistent PTJ reference; run the approved organisational reference seeder first.');
                }
                $parents[$row['Kod PTJ']] = $parent;
                if ($row['Kategori'] === 'PPD') {
                    $ppds[$row['PTJ']] = $parent;
                } elseif ($row['Kategori'] === 'SMK PTJ') {
                    $schoolPtjs[] = $parent;
                }
            }

            $records = [];
            foreach ($sectors as $row) {
                $parent = $parents[$row['Kod PTJ Induk']] ?? null;
                if ($parent === null || $parent->nama !== $row['PTJ'] ||
                    ! in_array($parent->kategori, ['JPNT', 'PPD'], true)) {
                    throw new RuntimeException('Invalid Sektor parent PTJ.');
                }
                $records[] = ['ptj_id' => $parent->id, 'jenis' => 'sektor', 'nama' => $row['Sektor']];
            }
            foreach ($schools as $row) {
                $parent = $ppds[$row['PPD']] ?? null;
                if ($parent === null) {
                    throw new RuntimeException('Unmatched non-PTJ school parent PPD.');
                }
                $records[] = ['ptj_id' => $parent->id, 'jenis' => 'sekolah_non_ptj', 'nama' => $row['Tempat Bertugas']];
            }
            foreach ($schoolPtjs as $parent) {
                $records[] = ['ptj_id' => $parent->id, 'jenis' => 'smk_ptj', 'nama' => $parent->nama];
            }

            $seen = [];
            foreach ($records as $record) {
                $key = json_encode(array_values($record), JSON_THROW_ON_ERROR);
                if (trim($record['nama']) === '' || isset($seen[$key])) {
                    throw new RuntimeException('Empty or duplicate workplace reference.');
                }
                $seen[$key] = true;
            }
            // Validate the complete input before writing; reruns preserve stable IDs.
            foreach ($records as $record) {
                TempatBertugas::firstOrCreate($record);
            }
        });
    }

    /** @return list<array<string, string>> */
    private function readCsv(string $file, array $headers): array
    {
        $handle = fopen(base_path('docs/references/'.$file), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Missing workplace reference CSV: '.$file);
        }
        try {
            if (fgetcsv($handle, 0, ',', '"', '') !== $headers) {
                throw new RuntimeException('Unexpected workplace reference CSV headers: '.$file);
            }
            $rows = [];
            while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($values) !== count($headers)) {
                    throw new RuntimeException('Invalid workplace reference CSV row: '.$file);
                }
                $rows[] = array_combine($headers, $values);
            }
            if ($rows === []) {
                throw new RuntimeException('Empty workplace reference CSV: '.$file);
            }
            return $rows;
        } finally {
            fclose($handle);
        }
    }
}
