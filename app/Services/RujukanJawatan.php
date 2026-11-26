<?php

namespace App\Services;

use RuntimeException;

/** Shared personnel references from Reference 07. */
class RujukanJawatan
{
    public function names(): array
    {
        return array_keys($this->grades());
    }

    public function grades(): array
    {
        return array_map('array_keys', $this->salaryRates());
    }

    public function salaryRates(): array
    {
        $handle = fopen(base_path('docs/references/jawatan-gred-gaji-kgt.csv'), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Rujukan Jawatan tidak tersedia.');
        }

        try {
            if (fgetcsv($handle, 0, ',', '"', '') !== ['KLASIFIKASI PERKHIDMATAN', 'SKIM PERKHIDMATAN/ JAWATAN', 'SSPA', 'GAJI MINIMUM', 'GAJI MAKSIMUM', 'GAJI MEDIAN', 'KGT']) {
                throw new RuntimeException('Struktur rujukan Jawatan tidak sah.');
            }

            $rates = [];
            $count = 0;
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($row) !== 7 || $row[1] === '' || $row[2] === '' || isset($rates[$row[1]][$row[2]])
                    || ! preg_match('/^\d+(?:\.\d{1,2})?$/D', $row[5])
                    || ($row[6] !== '' && ! preg_match('/^\d+(?:\.\d{1,2})?$/D', $row[6]))) {
                    throw new RuntimeException('Rekod Jawatan tidak sah.');
                }

                $rates[$row[1]][$row[2]] = ['median' => $row[5], 'kgt' => $row[6] === '' ? null : $row[6]];
                $count++;
            }

            if ($count !== 131) {
                throw new RuntimeException('Rujukan Jawatan tidak lengkap.');
            }

            ksort($rates, SORT_STRING);

            return $rates;
        } finally {
            fclose($handle);
        }
    }
}
