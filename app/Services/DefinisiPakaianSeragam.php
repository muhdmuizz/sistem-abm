<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

class DefinisiPakaianSeragam extends DefinisiAnggaran
{
    public const VERSION='os27000.pakaian-seragam.v2';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek==='27000' && !$os->dikecualikan && $os->butiran_perbelanjaan==='Pakaian Seragam';
    }

    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Jenis Bekalan','type'=>'select','options'=>'references','width'=>190],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>app(RujukanJawatan::class)->names(),'width'=>270],
            ['key'=>'bil_unit','label'=>'Bil. Pengisian','type'=>'decimal','width'=>130],
            ['key'=>'kekerapan','label'=>'Bil. Pasang','type'=>'output','value'=>'2','numeric'=>true,'width'=>120],
            ['key'=>'unit','label'=>'Unit','type'=>'output','value'=>'Pasang','width'=>90],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'output','reference'=>'kadar','numeric'=>true,'money'=>true,'width'=>140],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,'money'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kekerapan','kadar']],'width'=>200],
        ];
    }

    public function selector(): string { return 'kategori1'; }
    // Secondary lookup attribute is a personnel field, not a dependent category level.
    public function childSelector(): string { return 'jawatan'; }
    public function inputHelp(): string { return 'Jawatan menggunakan rujukan bersama. Kadar mengikut Jenis Bekalan dan Jawatan; Unit kekal Pasang; Bil. Pasang ditetapkan kepada 2 dan tidak boleh dipinda.'; }

    public function references(): array
    {
        $names=app(RujukanJawatan::class)->names();
        $handle=fopen(base_path('docs/references/OS27000-pakaian-seragam-kadar.csv'),'rb');
        if($handle===false)throw new RuntimeException('Rujukan Pakaian Seragam tidak tersedia.');
        try {
            if(fgetcsv($handle,0,',','"','')!==['Kategori1','Jawatan','Kadar (RM)','Kadar Mode'])throw new RuntimeException('Struktur rujukan Pakaian Seragam tidak sah.');
            $result=[];$count=0;
            while(($row=fgetcsv($handle,0,',','"',''))!==false){
                if(count($row)!==4)throw new RuntimeException('Rekod Pakaian Seragam tidak sah.');
                $job=strtoupper($row[1]);
                if(!in_array($job,$names,true)||!in_array($row[0],['Pakaian Seragam','Kasut'],true)||
                    !preg_match('/^\d+\.\d{2}$/D',$row[2])||$row[3]!=='JAWATAN_REFERENCE_LOOKUP'||isset($result[$row[0]][$job]))throw new RuntimeException('Padanan rujukan Pakaian Seragam tidak sah.');
                $result[$row[0]][$job]=['kadar'=>$row[2]];$count++;
            }
            if($count!==4||count($result)!==2||count($result['Pakaian Seragam'] ?? [])!==2||count($result['Kasut'] ?? [])!==2)throw new RuntimeException('Rujukan Pakaian Seragam tidak lengkap.');
            return $result;
        } finally { fclose($handle); }
    }

    public function validate(array $rows): array
    {
        $decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        return Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,kategori1,jawatan,bil_unit',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($this->references()))],
            'rekod.*.jawatan'=>['nullable','string',Rule::in(app(RujukanJawatan::class)->names())],
            'rekod.*.bil_unit'=>$decimal,
        ],['in'=>'Pilih nilai daripada rujukan yang sah.','regex'=>'Gunakan nombor perpuluhan biasa tanpa pemisah ribu.','array'=>'Struktur baris tidak sah.'])->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        return (object)['definition'=>self::VERSION,'kategori1'=>$row['kategori1'] ?? null,'jawatan'=>$row['jawatan'] ?? null,
            'bil_unit'=>$row['bil_unit'] ?? null,'kekerapan'=>'2'];
    }

    public function readable(mixed $source): bool
    {
        $keys=['definition','kategori1','jawatan','bil_unit','kekerapan'];
        if(!$source instanceof stdClass||!in_array($source->definition ?? null,[self::VERSION,'os27000.pakaian-seragam.v1'],true)||
            array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        if($source->definition===self::VERSION && $source->kekerapan!=='2')return false;
        if($source->definition==='os27000.pakaian-seragam.v1' && $source->kekerapan!==null && $source->kekerapan!=='' && (!is_string($source->kekerapan)||strlen($source->kekerapan)>128||!preg_match('/^-?\d+(?:\.\d+)?$/D',$source->kekerapan)))return false;
        $row=get_object_vars($source);unset($row['definition'],$row['kekerapan']);$row['tempat_bertugas_id']=1;
        try { $this->validate([$row]); } catch(\Illuminate\Validation\ValidationException) { return false; }
        return true;
    }

    public function preview(stdClass $source): ?string
    {
        $rate=$this->references()[$source->kategori1 ?? ''][$source->jawatan ?? '']['kadar'] ?? null;
        $factors=[$source->bil_unit ?? null,'2',$rate];
        if(in_array(null,$factors,true)||in_array('',$factors,true))return null;
        $value=BigDecimal::of('1');foreach($factors as $factor)$value=$value->multipliedBy($factor);
        $text=(string)$value;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
