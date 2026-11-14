<?php

namespace Tests\Unit;

use App\Support\PerbelanjaanColumns;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;

final class PerbelanjaanColumnsTest extends TestCase
{
    public function test_canonical_export_columns_are_exactly_thirty_in_order(): void
    {
        $headers = PerbelanjaanColumns::headers();

        self::assertSame(range(1, 30), array_keys($headers));
        self::assertCount(30, array_unique(array_values($headers)));
        self::assertSame('TARIKH MULA KONTRAK KESELURUHAN', $headers[8]);
        self::assertSame('TARIKH TAMAT KONTRAK KESELURUHAN', $headers[9]);
        self::assertSame('TEMPOH KONTRAK KESELURUHAN', $headers[10]);
        self::assertSame('ANGGARAN JUMLAH SETAHUN', $headers[27]);
        self::assertSame('KATEGORI PESERTA', $headers[30]);
    }

    public function test_variant_labels_do_not_mutate_export_headers(): void
    {
        self::assertSame('BIL. ANAK', PerbelanjaanColumns::displayLabel(19, '42000:taska'));
        self::assertSame('NAMA IBU/ BAPA', PerbelanjaanColumns::displayLabel(11, '42000:taska'));
        self::assertSame('BIL. SALINAN', PerbelanjaanColumns::displayLabel(19, '29000:bacaan-meter'));
        self::assertSame('LOKASI MESIN FOTOSTAT', PerbelanjaanColumns::displayLabel(20, '24000:mesin-fotostat'));
        self::assertSame('BIL. UNIT', PerbelanjaanColumns::headers()[19]);
        self::assertSame('NAMA', PerbelanjaanColumns::headers()[11]);
    }

    public function test_overall_contract_fields_are_restricted_to_approved_variants(): void
    {
        self::assertTrue(PerbelanjaanColumns::allowsOverallContract('24000:mesin-fotostat'));
        self::assertTrue(PerbelanjaanColumns::allowsOverallContract('29000:bacaan-meter'));
        self::assertFalse(PerbelanjaanColumns::allowsOverallContract('29000:mystep'));
        self::assertFalse(PerbelanjaanColumns::allowsOverallContract('42000:taska'));
        self::assertFalse(PerbelanjaanColumns::allowsOverallContract('42000:sbk'));
    }

    public function test_unknown_column_position_is_rejected(): void
    {
        $this->expectException(OutOfRangeException::class);

        PerbelanjaanColumns::displayLabel(31);
    }
}
