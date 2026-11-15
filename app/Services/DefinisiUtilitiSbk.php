<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Approved shared SBK Pukal template. This is a draft adapter, not official output. */
class DefinisiUtilitiSbk extends DefinisiAnggaran
{
    public const VERSION = 'os42000.sbk.v1';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek === '42000' && ! $os->dikecualikan &&
            $os->butiran_perbelanjaan === 'Bayaran Bil Utiliti Sekolah Bantuan Kerajaan (SBK)';
    }

    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori2','label'=>'Kategori2 — Utiliti','type'=>'select','options'=>'references','width'=>190],
            ['key'=>'kadar','money'=>true,'label'=>'Kadar Bulanan (RM)','type'=>'decimal','width'=>180],
            ['key'=>'kategori1','label'=>'Kategori1','type'=>'output','value'=>'Pukal','width'=>110],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'output','value'=>'12','numeric'=>true,'width'=>130],
            ['key'=>'preview','money'=>true,'label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['kadar','bil_bulan']],'width'=>200],
        ];
    }

    public function selector(): string { return 'kategori2'; }

    public function inputHelp(): string
    {
        return 'Kadar ialah anggaran bulanan yang diisi sendiri. Kategori1 kekal Pukal; Bilangan Bulan kekal 12.';
    }

    public function references(): array
    {
        $handle = fopen(base_path('docs/references/OS23000-perhubungan-utiliti-kategori.csv'), 'rb');
        if ($handle === false) { throw new RuntimeException('Rujukan utiliti tidak tersedia.'); }
        try {
            if (fgetcsv($handle, 0, ',', '"', '') !== ['Kategori1','Kategori2','Kadar Mode']) {
                throw new RuntimeException('Struktur rujukan utiliti tidak sah.');
            }
            $rows = []; $count = 0;
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($row) !== 3 || $row[2] !== 'USER_INPUT_KADAR' ||
                    ! in_array($row[0], ['Pukal','Bukan Pukal'], true)) {
                    throw new RuntimeException('Rekod rujukan utiliti tidak sah.');
                }
                $count++;
                if ($row[0] !== 'Pukal') { continue; }
                if (isset($rows[$row[1]])) { throw new RuntimeException('Rujukan utiliti berulang.'); }
                $rows[$row[1]] = [];
            }
            if ($count !== 7 || array_keys($rows) !== ['Air','Elektrik','Telefon/ Faks','Pembetungan']) {
                throw new RuntimeException('Rujukan Pukal tidak lengkap.');
            }
            return $rows;
        } finally { fclose($handle); }
    }

    public function validate(array $rows): array
    {
        foreach($rows as &$row) { if(is_array($row)&&array_key_exists('kadar',$row))$row['kadar']=SumberWang::normalise($row['kadar']); } unset($row);
        return Validator::make(['rekod'=>$rows], [
            'rekod'=>'present|array',
            'rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,kategori2,kadar',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct',
            'rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori2'=>['nullable','string',Rule::in(array_keys($this->references()))],
            // Draft source text only: no invented precision, eligibility or rounding policy.
            'rekod.*.kadar'=>['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'],
        ], ['required'=>':attribute diperlukan.','in'=>'Pilih utiliti Pukal yang sah.',
            'regex'=>'Gunakan kadar bulanan dalam nombor perpuluhan biasa tanpa pemisah ribu.',
            'integer'=>'Gunakan nombor bulat yang sah.','array'=>'Struktur baris tidak sah.'])->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        return (object)['definition'=>self::VERSION,'kategori2'=>$row['kategori2'] ?? null,'kadar'=>$row['kadar'] ?? null];
    }

    public function readable(mixed $source): bool
    {
        if($source instanceof stdClass && SumberWang::normalise($source->kadar ?? null)!==($source->kadar ?? null))return false;
        if (! $source instanceof stdClass || ($source->definition ?? null) !== self::VERSION ||
            array_diff(['definition','kategori2','kadar'], array_keys(get_object_vars($source))) ||
            array_diff(array_keys(get_object_vars($source)), ['definition','kategori2','kadar'])) { return false; }
        try { $this->validate([['tempat_bertugas_id'=>1,'kategori2'=>$source->kategori2,'kadar'=>$source->kadar]]); }
        catch (\Illuminate\Validation\ValidationException) { return false; }
        return true;
    }

    public function preview(stdClass $source): ?string
    {
        if (! isset($this->references()[$source->kategori2 ?? '']) ||
            ($source->kadar ?? null) === null || $source->kadar === '') { return null; }
        $value = (string) BigDecimal::of($source->kadar)->multipliedBy('12');
        return str_contains($value, '.') ? rtrim(rtrim($value, '0'), '.') : $value;
    }
}
