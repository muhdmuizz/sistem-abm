<?php

namespace App\Support;

/**
 * Canonical Perbelanjaan export/editor column identities from AD-008.
 *
 * This class provides shared structure, not OS-specific applicability,
 * rate policy, formulas or permission to submit an Anggaran.
 */
final class PerbelanjaanColumns
{
    public const HEADERS = [
        1 => 'OBJEK SEBAGAI',
        2 => 'JPNT/ PPD',
        3 => 'SEKTOR/ SEKOLAH',
        4 => 'NAMA PROGRAM/ AKTIVITI/ TUJUAN',
        5 => 'TARIKH MULA',
        6 => 'TARIKH TAMAT',
        7 => 'TEMPOH',
        8 => 'TARIKH MULA KONTRAK KESELURUHAN',
        9 => 'TARIKH TAMAT KONTRAK KESELURUHAN',
        10 => 'TEMPOH KONTRAK KESELURUHAN',
        11 => 'NAMA',
        12 => 'JAWATAN',
        13 => 'GRED',
        14 => 'KATEGORI1',
        15 => 'KATEGORI2',
        16 => 'KATEGORI3',
        17 => 'UNIT',
        18 => 'GRED PENCERAMAH/ FASILITATOR',
        19 => 'BIL. UNIT',
        20 => 'LOKASI URUSAN RASMI',
        21 => 'KADAR',
        22 => 'JARAK',
        23 => 'KEKERAPAN',
        24 => 'JAM',
        25 => 'BILANGAN HARI',
        26 => 'BILANGAN BULAN',
        27 => 'ANGGARAN JUMLAH SETAHUN',
        28 => 'KATEGORI SUMBER PERUNTUKAN',
        29 => 'KATEGORI PROGRAM',
        30 => 'KATEGORI PESERTA',
    ];

    /** Display only: the official export header is always unchanged. */
    public const DISPLAY_LABELS = [
        '24000:mesin-fotostat' => [
            5 => 'TARIKH MULA KONTRAK SEMASA',
            6 => 'TARIKH TAMAT KONTRAK SEMASA',
            7 => 'TEMPOH KONTRAK SEMASA',
            20 => 'LOKASI MESIN FOTOSTAT',
        ],
        '29000:bacaan-meter' => [
            5 => 'TARIKH MULA KONTRAK SEMASA',
            6 => 'TARIKH TAMAT KONTRAK SEMASA',
            7 => 'TEMPOH KONTRAK SEMASA',
            19 => 'BIL. SALINAN',
            20 => 'LOKASI MESIN FOTOSTAT',
        ],
        '42000:taska' => [
            11 => 'NAMA IBU/ BAPA',
            19 => 'BIL. ANAK',
        ],
    ];

    /** @return array<int, string> */
    public static function headers(): array
    {
        return self::HEADERS;
    }

    public static function displayLabel(int $position, ?string $variant = null): string
    {
        if (! array_key_exists($position, self::HEADERS)) {
            throw new \OutOfRangeException('Unknown Perbelanjaan column position.');
        }

        return self::DISPLAY_LABELS[$variant ?? ''][$position] ?? self::HEADERS[$position];
    }

    /** Exactly these approved variants may populate overall contract columns 08–10. */
    public static function allowsOverallContract(string $variant): bool
    {
        return in_array($variant, ['24000:mesin-fotostat', '29000:bacaan-meter'], true);
    }
}
