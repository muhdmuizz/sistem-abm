<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RpmReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(base_path('docs/references/rpm-objectives.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->validateLevel($data, 'strategi');
        $counts = [count($data), 0, 0];
        foreach ($data as $teras) {
            $counts[1] += count($teras['strategi']);
            foreach ($teras['strategi'] as $strategi) { $counts[2] += count($strategi['prakarsa']); }
        }
        if ($counts !== [7,25,44]) { throw new RuntimeException('Unexpected approved RPM reference inventory.'); }
        DB::transaction(function () use ($data) {
            DB::select('select pg_advisory_xact_lock(41620101, 8020)');
            foreach ($data as $teras) {
                $terasId = $this->reference('teras_strategik', ['nombor' => $teras['nombor']], $teras['tajuk']);
                foreach ($teras['strategi'] as $strategi) {
                    $strategiId = $this->reference('strategi_rpm', ['teras_strategik_id' => $terasId,
                        'nombor' => $strategi['nombor']], $strategi['tajuk']);
                    foreach ($strategi['prakarsa'] as $prakarsa) {
                        $this->reference('prakarsa_rpm', ['strategi_id' => $strategiId, 'nombor' => $prakarsa['nombor']], $prakarsa['tajuk']);
                    }
                }
            }
        });
    }

    private function validateLevel(array $rows, ?string $children): void
    {
        if (! array_is_list($rows) || $rows === []) { throw new RuntimeException('Invalid RPM reference list.'); }
        $seen = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ! is_int($row['nombor'] ?? null) || $row['nombor'] < 1 ||
                isset($seen[$row['nombor']]) || ! is_string($row['tajuk'] ?? null) || trim($row['tajuk']) === '') {
                throw new RuntimeException('Invalid or duplicated RPM reference identity.');
            }
            $seen[$row['nombor']] = true;
            if ($children !== null) {
                if (! isset($row[$children]) || ! is_array($row[$children])) { throw new RuntimeException('Missing RPM reference children.'); }
                $this->validateLevel($row[$children], $children === 'strategi' ? 'prakarsa' : null);
            }
        }
    }

    private function reference(string $table, array $identity, string $title): int
    {
        if (trim($title) === '' || ! is_int($identity['nombor']) || $identity['nombor'] < 1) {
            throw new RuntimeException('Invalid RPM reference row.');
        }
        $existing = DB::table($table)->where($identity)->first();
        if ($existing) {
            if ($existing->tajuk !== $title) { DB::table($table)->where('id', $existing->id)->update(['tajuk' => $title, 'updated_at' => now()]); }
            return $existing->id;
        }
        return DB::table($table)->insertGetId($identity + ['tajuk' => $title, 'created_at' => now(), 'updated_at' => now()]);
    }
}
