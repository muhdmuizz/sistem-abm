<?php

namespace Database\Seeders;

use App\Models\AktivitiKewangan;
use App\Models\KodAktiviti;
use App\Models\Ptj;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrganisationalReferenceSeeder extends Seeder
{
    /**
     * Import approved reference records only. Never delete user-created records.
     */
    public function run(): void
    {
        $ptjs = $this->readCsv('docs/references/ptj-master.csv', [
            'Kod PTJ', 'PTJ', 'Kategori', 'Daerah',
        ]);
        $codes = $this->readCsv('docs/references/aktiviti-kewangan-kod-aktiviti.csv', [
            'Aktiviti Kewangan', 'Singkatan Sumber', 'Kod Aktiviti',
        ]);

        $seenPtjs = [];
        foreach ($ptjs as $row) {
            $code = $row['Kod PTJ'];
            if (! preg_match('/^[A-Za-z0-9]{8}$/D', $code) || isset($seenPtjs[$code])) {
                throw new RuntimeException('Invalid or duplicated PTJ code in reference CSV.');
            }
            if (! in_array($row['Kategori'], ['JPNT', 'PPD', 'SMK PTJ'], true) ||
                $row['PTJ'] === '') {
                throw new RuntimeException('Invalid PTJ category or name in reference CSV.');
            }
            $seenPtjs[$code] = true;
        }

        $seenCodes = [];
        $activities = [];
        foreach ($codes as $row) {
            $code = $row['Kod Aktiviti'];
            $name = $row['Aktiviti Kewangan'];
            if (! preg_match('/^[0-9]{6}$/D', $code) || isset($seenCodes[$code]) ||
                $name === '') {
                throw new RuntimeException('Invalid or duplicated Kod Aktiviti in reference CSV.');
            }
            $seenCodes[$code] = true;
            $activities[$name] = $name === 'Pengurusan Pentadbiran Negeri'
                ? 'PPN'
                : null;
        }

        DB::transaction(function () use ($ptjs, $codes, $activities): void {
            foreach ($ptjs as $row) {
                Ptj::updateOrCreate(
                    ['kod_ptj' => $row['Kod PTJ']],
                    [
                        'nama' => $row['PTJ'],
                        'kategori' => $row['Kategori'],
                        'daerah' => $row['Daerah'] !== '' ? $row['Daerah'] : null,
                    ],
                );
            }

            foreach ($activities as $name => $abbreviation) {
                AktivitiKewangan::updateOrCreate(
                    ['nama' => $name],
                    ['singkatan' => $abbreviation],
                );
            }

            $activityIds = AktivitiKewangan::query()
                ->whereIn('nama', array_keys($activities))
                ->pluck('id', 'nama');

            foreach ($codes as $row) {
                KodAktiviti::updateOrCreate(
                    ['kod_aktiviti' => $row['Kod Aktiviti']],
                    ['aktiviti_kewangan_id' => $activityIds[$row['Aktiviti Kewangan']]],
                );
            }
        });
    }

    /**
     * @param list<string> $expectedHeaders
     * @return list<array<string, string>>
     */
    private function readCsv(string $path, array $expectedHeaders): array
    {
        $handle = fopen(base_path($path), 'rb');
        if ($handle === false) {
            throw new RuntimeException("Missing reference CSV: {$path}");
        }

        try {
            $headers = fgetcsv($handle, 0, ',', '"', '');
            if ($headers !== $expectedHeaders) {
                throw new RuntimeException("Unexpected reference CSV headers: {$path}");
            }

            $result = [];
            while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($values) !== count($headers)) {
                    throw new RuntimeException("Invalid reference CSV row: {$path}");
                }
                $result[] = array_combine($headers, $values);
            }
            if ($result === []) {
                throw new RuntimeException("Empty reference CSV: {$path}");
            }

            return $result;
        } finally {
            fclose($handle);
        }
    }
}
