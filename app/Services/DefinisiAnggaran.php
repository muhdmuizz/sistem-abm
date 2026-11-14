<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Typed source definitions for the one shared editor; not an official financial output engine. */
class DefinisiAnggaran
{
    public const VERSION = 'os25000.v1';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek === '25000' && $os->butiran_perbelanjaan === 'Bahan-bahan Makanan dan Minuman' && ! $os->dikecualikan;
    }

    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Jenis Bekalan','type'=>'select','options'=>'references','width'=>200],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>120],
            ['key'=>'unit','label'=>'UNIT','type'=>'output','reference'=>'unit','width'=>130],
            ['key'=>'kadar','label'=>'KADAR (RM)','type'=>'output','reference'=>'kadar','numeric'=>true,'width'=>110],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kadar']],'width'=>160],
        ];
    }
    public function selector(): string { return 'kategori1'; }
    public function project(stdClass $source): array
    {
        $result=[];
        foreach($this->columns() as $column) { if($column['type']!=='output' && !($column['context'] ?? false))$result[$column['key']]=$source->{$column['key']} ?? null; }
        return $result;
    }

    public function references(): array
    {
        $handle = fopen(base_path('docs/references/OS25000-bahan-makanan-minuman-kategori.csv'), 'rb');
        if ($handle === false) { throw new RuntimeException('Rujukan OS25000 tidak tersedia.'); }
        try {
            if (fgetcsv($handle, 0, ',', '"', '') !== ['Kategori1','Unit','Kadar (RM)','Unit Mode','Kadar Mode']) {
                throw new RuntimeException('Struktur rujukan OS25000 tidak sah.');
            }
            $rows = [];
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($row) !== 5 || isset($rows[$row[0]]) || $row[3] !== 'AUTO_REFERENCE' ||
                    $row[4] !== 'FIXED_REFERENCE' || !preg_match('/^\d+\.\d{2}$/D',$row[2])) {
                    throw new RuntimeException('Rekod rujukan OS25000 tidak sah.');
                }
                $rows[$row[0]] = ['unit'=>$row[1], 'kadar'=>$row[2]];
            }
            if (count($rows) !== 4) { throw new RuntimeException('Rujukan OS25000 tidak lengkap.'); }
            return $rows;
        } finally { fclose($handle); }
    }

    public function validate(array $rows): array
    {
        $references = $this->references();
        return Validator::make(['rekod'=>$rows], [
            'rekod'=>'present|array',
            'rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,kategori1,bil_unit',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct',
            'rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($references))],
            // Technical text-size limit only. No new financial precision/minimum policy.
            'rekod.*.bil_unit'=>['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'],
        ], ['required'=>':attribute diperlukan.','in'=>'Pilih kategori yang sah.',
            'regex'=>'Gunakan nombor perpuluhan biasa tanpa pemisah ribu.',
            'integer'=>'Gunakan nombor bulat yang sah.','array'=>'Struktur baris tidak sah.'])->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        return (object)['definition'=>self::VERSION,'kategori1'=>$row['kategori1'] ?? null,'bil_unit'=>$row['bil_unit'] ?? null];
    }

    public function readable(mixed $source): bool
    {
        if (!$source instanceof stdClass || array_diff(['definition','kategori1','bil_unit'],array_keys(get_object_vars($source))) || ($source->definition ?? null)!==self::VERSION ||
            array_diff(array_keys(get_object_vars($source)),['definition','kategori1','bil_unit'])) { return false; }
        try { $this->validate([['tempat_bertugas_id'=>1,'kategori1'=>$source->kategori1 ?? null,'bil_unit'=>$source->bil_unit ?? null]]); }
        catch (\Illuminate\Validation\ValidationException) { return false; }
        return true;
    }

    public function preview(stdClass $source): ?string
    {
        $reference = $this->references()[$source->kategori1 ?? ''] ?? null;
        if (!$reference || ($source->bil_unit ?? null)===null || $source->bil_unit==='') { return null; }
        $value = (string)BigDecimal::of($source->bil_unit)->multipliedBy($reference['kadar']);
        return str_contains($value, '.') ? rtrim(rtrim($value, '0'), '.') : $value;
    }
}
