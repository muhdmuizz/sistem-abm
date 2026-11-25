<?php

namespace App\Services;

use Phar;
use PharData;
use RuntimeException;

class EksportRumusanGaji
{
    private const HEADINGS = [
        '#', 'Tempat Bertugas', 'Bil. Perjawatan', 'Bil. Pengisian', 'Bil. Kekosongan',
        'OS11000 (RM)', 'OS12000 (RM)', 'OS13000 (RM)', 'Jumlah',
    ];

    public function workbook(array $workplaces, array $rows): string
    {
        $total = array_fill(0, 7, 0);
        $sheetRows = [$this->row(1, self::HEADINGS)];
        foreach ($rows as $index => $row) {
            $values = array_map(intval(...), [...$row['counts'], ...$row['amounts']]);
            $values[] = array_sum(array_slice($values, 3));
            foreach ($values as $column => $value) {
                $total[$column] += $value;
            }
            $sheetRows[] = $this->row($index + 2, [$index + 1, $workplaces[$row['id']], ...$values]);
        }
        $sheetRows[] = $this->row(count($rows) + 2, [null, 'Jumlah', ...$total]);

        $path = tempnam(sys_get_temp_dir(), 'abm-rumusan-');
        if ($path === false) {
            throw new RuntimeException('Could not allocate the workbook.');
        }

        try {
            $archive = new PharData($path.'.zip', format: Phar::ZIP);
            foreach ($this->parts(implode('', $sheetRows)) as $name => $xml) {
                $archive->addFromString($name, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.$xml);
            }
            unset($archive);
            $bytes = file_get_contents($path.'.zip');
            if ($bytes === false) {
                throw new RuntimeException('Could not read the workbook.');
            }

            return $bytes;
        } finally {
            if (is_file($path.'.zip')) {
                unlink($path.'.zip');
            }
            unlink($path);
        }
    }

    private function row(int $number, array $values): string
    {
        $cells = '';
        foreach ($values as $index => $value) {
            if ($value === null) {
                continue;
            }
            $ref = chr(65 + $index).$number;
            if (is_string($value)) {
                $text = htmlspecialchars($value, ENT_XML1 | ENT_SUBSTITUTE | ENT_DISALLOWED, 'UTF-8');
                $cells .= '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'.$text.'</t></is></c>';
            } elseif ($index >= 5) {
                $amount = intdiv($value, 100).'.'.str_pad((string) ($value % 100), 2, '0', STR_PAD_LEFT);
                $cells .= '<c r="'.$ref.'" s="1"><v>'.$amount.'</v></c>';
            } else {
                $cells .= '<c r="'.$ref.'"><v>'.$value.'</v></c>';
            }
        }

        return '<row r="'.$number.'">'.$cells.'</row>';
    }

    private function parts(string $rows): array
    {
        return [
            '[Content_Types].xml' => <<<'XML'
            <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
                <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
                <Default Extension="xml" ContentType="application/xml"/>
                <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
                <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
                <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
            </Types>
            XML,
            '_rels/.rels' => <<<'XML'
            <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
            </Relationships>
            XML,
            'xl/workbook.xml' => <<<'XML'
            <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
                <sheets>
                    <sheet name="Rumusan" sheetId="1" r:id="rId1"/>
                </sheets>
            </workbook>
            XML,
            'xl/_rels/workbook.xml.rels' => <<<'XML'
            <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
                <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
            </Relationships>
            XML,
            'xl/styles.xml' => <<<'XML'
            <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
                <fonts count="1">
                    <font/>
                </fonts>
                <fills count="2">
                    <fill>
                        <patternFill patternType="none"/>
                    </fill>
                    <fill>
                        <patternFill patternType="gray125"/>
                    </fill>
                </fills>
                <borders count="1">
                    <border/>
                </borders>
                <cellStyleXfs count="1">
                    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
                </cellStyleXfs>
                <cellXfs count="2">
                    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
                    <xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
                </cellXfs>
                <cellStyles count="1">
                    <cellStyle name="Normal" xfId="0" builtinId="0"/>
                </cellStyles>
            </styleSheet>
            XML,
            'xl/worksheets/sheet1.xml' => <<<XML
            <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
                <cols>
                    <col min="1" max="1" width="6" customWidth="1"/>
                    <col min="2" max="2" width="40" customWidth="1"/>
                    <col min="3" max="9" width="19" customWidth="1"/>
                </cols>
                <sheetData>
                    {$rows}
                </sheetData>
            </worksheet>
            XML,
        ];
    }
}
