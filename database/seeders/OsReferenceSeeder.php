<?php

namespace Database\Seeders;

use App\Models\AktivitiKewangan;
use App\Models\KodAktiviti;
use App\Models\PemetaanOs;
use App\Models\RujukanOs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OsReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = $this->readCsv('anggaran-os-catalogue.csv', [
            'Kod Objek', 'Butiran Perbelanjaan (Canonical)', 'Status Implementasi',
        ]);
        $mappings = $this->readCsv('kod-aktiviti-anggaran-os-mapping.csv', [
            'Aktiviti Kewangan', 'Singkatan Sumber', 'Kod Aktiviti', 'Kod Objek',
            'Butiran Perbelanjaan (Canonical)', 'Butiran Perbelanjaan (Original)', 'Status Implementasi',
        ]);

        DB::transaction(function () use ($catalogue, $mappings): void {
            $references = [];
            foreach ($catalogue as $row) {
                $key = $this->identity($row);
                if (isset($references[$key]) || ! in_array($row['Status Implementasi'], [
                    'Eligible reference', 'Excluded — reference only',
                ], true)) {
                    throw new RuntimeException('Duplicate OS identity or unknown catalogue status.');
                }
                $references[$key] = $row;
            }
            $activities = AktivitiKewangan::pluck('id', 'nama');
            $codes = KodAktiviti::all()->keyBy('kod_aktiviti');
            $prepared = [];
            $seen = [];
            foreach ($mappings as $row) {
                $key = $this->identity($row);
                $activityId = $activities[$row['Aktiviti Kewangan']] ?? null;
                $code = $row['Kod Aktiviti'] === '' ? null : ($codes[$row['Kod Aktiviti']] ?? null);
                if (! isset($references[$key]) || $activityId === null) {
                    throw new RuntimeException('Unmatched OS or Aktiviti Kewangan reference.');
                }
                if ($row['Kod Aktiviti'] !== '' && (! preg_match('/^[0-9]{6}$/D', $row['Kod Aktiviti']) ||
                    $code === null || $code->aktiviti_kewangan_id !== $activityId)) {
                    throw new RuntimeException('Missing or mismatched Kod Aktiviti parent.');
                }
                $excluded = $references[$key]['Status Implementasi'] === 'Excluded — reference only';
                $expectedStatuses = $excluded ? ['Excluded — reference only'] : [
                    'Eligible reference', 'User-confirmed mapping — shared Kod Aktiviti with Sukan',
                ];
                if (! in_array($row['Status Implementasi'], $expectedStatuses, true)) {
                    throw new RuntimeException('Inconsistent OS mapping status.');
                }
                $mappingKey = json_encode([$activityId, $row['Kod Aktiviti'], $key], JSON_THROW_ON_ERROR);
                if (isset($seen[$mappingKey])) {
                    throw new RuntimeException('Duplicate source OS mapping.');
                }
                $seen[$mappingKey] = true;
                $prepared[] = [$row, $key, $activityId, $code?->id];
            }

            // Validate all input and parents before importing either table.
            $ids = [];
            foreach ($references as $key => $row) {
                $reference = RujukanOs::updateOrCreate([
                    'kod_objek' => $row['Kod Objek'],
                    'butiran_perbelanjaan' => $row['Butiran Perbelanjaan (Canonical)'],
                ], ['dikecualikan' => $row['Status Implementasi'] === 'Excluded — reference only']);
                $ids[$key] = $reference->id;
            }
            foreach ($prepared as [$row, $key, $activityId, $codeId]) {
                // Owner-confirmed correction: upgrade only the prior PPN TASKA
                // blank-code mapping, retaining its stable ID and unrelated rows.
                if ($row['Kod Aktiviti'] === '010102' &&
                    $row['Aktiviti Kewangan'] === 'Pengurusan Pentadbiran Negeri' &&
                    $row['Kod Objek'] === '42000' &&
                    $row['Butiran Perbelanjaan (Canonical)'] === 'Subsidi Yuran TASKA') {
                    $legacy = PemetaanOs::where('aktiviti_kewangan_id', $activityId)
                        ->where('rujukan_os_id', $ids[$key])->whereNull('kod_aktiviti_id')
                        ->lockForUpdate()->first();
                    if ($legacy !== null) {
                        if (PemetaanOs::where('aktiviti_kewangan_id', $activityId)
                            ->where('rujukan_os_id', $ids[$key])->where('kod_aktiviti_id', $codeId)->exists()) {
                            throw new RuntimeException('Conflicting TASKA mappings require reconciliation; no records were changed.');
                        }
                        $legacy->update(['kod_aktiviti_id' => $codeId]);
                    }
                }
                PemetaanOs::updateOrCreate([
                    'aktiviti_kewangan_id' => $activityId,
                    'kod_aktiviti_id' => $codeId,
                    'rujukan_os_id' => $ids[$key],
                ], [
                    'singkatan_sumber' => $row['Singkatan Sumber'],
                    'butiran_asal' => $row['Butiran Perbelanjaan (Original)'],
                    'status_sumber' => $row['Status Implementasi'],
                ]);
            }
        });
    }

    private function identity(array $row): string
    {
        if (! preg_match('/^[0-9]{5}$/D', $row['Kod Objek']) ||
            trim($row['Butiran Perbelanjaan (Canonical)']) === '') {
            throw new RuntimeException('Invalid OS reference code or description.');
        }

        return json_encode([$row['Kod Objek'], $row['Butiran Perbelanjaan (Canonical)']], JSON_THROW_ON_ERROR);
    }

    /** @return list<array<string, string>> */
    private function readCsv(string $file, array $headers): array
    {
        $handle = fopen(base_path('docs/references/'.$file), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Missing OS reference CSV: '.$file);
        }
        try {
            if (fgetcsv($handle, 0, ',', '"', '') !== $headers) {
                throw new RuntimeException('Unexpected OS reference CSV headers: '.$file);
            }
            $rows = [];
            while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($values) !== count($headers)) {
                    throw new RuntimeException('Invalid OS reference CSV row: '.$file);
                }
                $rows[] = array_combine($headers, $values);
            }
            if ($rows === []) {
                throw new RuntimeException('Empty OS reference CSV: '.$file);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }
}
