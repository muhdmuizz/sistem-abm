<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use stdClass;

/** Approved TASKA budgeting source adapter; no entitlement or completeness decision. */
class DefinisiTaska extends DefinisiAnggaran
{
    public const VERSION = 'os42000.taska.v1';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek === '42000' && ! $os->dikecualikan &&
            $os->butiran_perbelanjaan === 'Subsidi Yuran TASKA';
    }

    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'nama','label'=>'Nama Ibu/ Bapa','type'=>'text','maxLength'=>500,'width'=>240],
            ['key'=>'bil_unit','label'=>'Bil. Anak','type'=>'decimal','width'=>130],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>140],
            ['key'=>'unit','label'=>'Unit','type'=>'output','value'=>'Anak','width'=>90],
            ['key'=>'kadar','money'=>true,'label'=>'Kadar Bulanan (RM/ Anak)','type'=>'output','reference'=>'kadar','referenceKey'=>'TASKA','numeric'=>true,'width'=>160],
            ['key'=>'preview','money'=>true,'label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kadar','bil_bulan']],'width'=>200],
        ];
    }

    public function selector(): string { return ''; }

    public function inputHelp(): string
    {
        return 'Isi nama ibu/ bapa, Bil. Anak dan bilangan bulan yang dianggarkan. Unit Anak dan Kadar bulanan diisi daripada rujukan.';
    }

    public function references(): array
    {
        $handle = fopen(base_path('docs/references/OS42000-subsidi-yuran-taska-kadar.csv'), 'rb');
        if ($handle === false) { throw new RuntimeException('Rujukan TASKA tidak tersedia.'); }
        try {
            $header = fgetcsv($handle, 0, ',', '"', '');
            $row = fgetcsv($handle, 0, ',', '"', '');
            if ($header !== ['Butiran Perbelanjaan','Kadar (RM)','Kadar Mode'] ||
                $row !== ['Subsidi Yuran TASKA','180.00','AUTO_REFERENCE'] ||
                fgetcsv($handle, 0, ',', '"', '') !== false) {
                throw new RuntimeException('Rujukan TASKA tidak sah.');
            }
            return ['TASKA'=>['kadar'=>$row[1]]];
        } finally { fclose($handle); }
    }

    public function validate(array $rows): array
    {
        $decimal = ['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        return Validator::make(['rekod'=>$rows], [
            'rekod'=>'present|array',
            'rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,nama,bil_unit,bil_bulan',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct',
            'rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.nama'=>'nullable|string|max:500',
            // Exact draft text; no additional financial precision/month cap policy.
            'rekod.*.bil_unit'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ], ['required'=>':attribute diperlukan.','regex'=>'Gunakan nombor perpuluhan biasa tanpa pemisah ribu.',
            'integer'=>'Gunakan nombor bulat yang sah.','array'=>'Struktur baris tidak sah.'])->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        return (object)['definition'=>self::VERSION,'nama'=>$row['nama'] ?? null,
            'bil_unit'=>$row['bil_unit'] ?? null,'bil_bulan'=>$row['bil_bulan'] ?? null];
    }

    public function readable(mixed $source): bool
    {
        $keys=['definition','nama','bil_unit','bil_bulan'];
        if (! $source instanceof stdClass || ($source->definition ?? null) !== self::VERSION ||
            array_diff($keys,array_keys(get_object_vars($source))) ||
            array_diff(array_keys(get_object_vars($source)),$keys)) { return false; }
        try { $this->validate([['tempat_bertugas_id'=>1,'nama'=>$source->nama,'bil_unit'=>$source->bil_unit,'bil_bulan'=>$source->bil_bulan]]); }
        catch (\Illuminate\Validation\ValidationException) { return false; }
        return true;
    }

    public function preview(stdClass $source): ?string
    {
        if (($source->bil_unit ?? null) === null || $source->bil_unit === '' ||
            ($source->bil_bulan ?? null) === null || $source->bil_bulan === '') { return null; }
        $value = (string) BigDecimal::of($source->bil_unit)->multipliedBy($this->references()['TASKA']['kadar'])
            ->multipliedBy($source->bil_bulan);
        return str_contains($value, '.') ? rtrim(rtrim($value, '0'), '.') : $value;
    }
}
