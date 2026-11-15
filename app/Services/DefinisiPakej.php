<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Approved direct Pakej source drafts; chargeable nights/days stay user-entered. */
class DefinisiPakej extends DefinisiAnggaran
{
    public const VERSION='os29000.pakej.v1';
    public function __construct(private int $year) {}
    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek==='29000' && !$os->dikecualikan && $os->butiran_perbelanjaan==='Pakej (Mesyuarat/ Bengkel/ Kursus)';
    }
    public function inputHelp(): string {return 'Kadar diisi mengikut asas setiap komponen. Penginapan menggunakan bilangan malam; komponen lain menggunakan hari bercaj. Tarikh dan Tempoh tidak menetapkan bilangan malam atau hari bercaj.';}
    public function columns(): array
    {
        $min=sprintf('%04d-01-01',$this->year);$max=sprintf('%04d-12-31',$this->year);
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Nama Program/ Aktiviti/ Tujuan','type'=>'text','maxLength'=>500,'width'=>280],
            ['key'=>'kategori1','label'=>'Kategori1 — Komponen Pakej','type'=>'select','options'=>'references','width'=>200],
            ['key'=>'tarikh_mula','label'=>'Tarikh Mula','type'=>'date','min'=>$min,'max'=>$max,'width'=>160],
            ['key'=>'tarikh_tamat','label'=>'Tarikh Tamat','type'=>'date','min'=>$min,'max'=>$max,'width'=>160],
            ['key'=>'tempoh','label'=>'Tempoh (Hari)','type'=>'output','numeric'=>true,'formula'=>['operation'=>'inclusiveDays','inputs'=>['tarikh_mula','tarikh_tamat']],'width'=>130],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>120],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'hintReference'=>'basis','width'=>210],
            ['key'=>'bil_hari','label'=>'Bilangan Hari/ Malam','type'=>'decimal','labelReference'=>'duration_label','hintReference'=>'duration_label','width'=>180],
            ['key'=>'unit','label'=>'Unit','type'=>'output','reference'=>'unit','width'=>100],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'inputs'=>['bil_unit','kadar','bil_hari']],'width'=>210],
        ];
    }
    public function references(): array
    {
        $approved=[
            'Penginapan'=>['unit'=>'Bilik','basis'=>'RM sebilik semalam','duration_label'=>'Bil. Malam'],
            'Makan Minum'=>['unit'=>'Orang','basis'=>'RM seorang sehari','duration_label'=>'Bilangan Hari'],
            'Sewaan Dewan'=>['unit'=>'Dewan','basis'=>'RM sedewan sehari','duration_label'=>'Bilangan Hari'],
            'Sewaan Peralatan'=>['unit'=>'Unit','basis'=>'RM seunit sehari','duration_label'=>'Bilangan Hari'],
        ];
        $handle=fopen(base_path('docs/references/OS29000-pakej-kategori.csv'),'rb');if($handle===false)throw new RuntimeException('Rujukan Pakej tidak tersedia.');
        try{
            if(fgetcsv($handle,0,',','"','')!==['Kategori1','Kadar Mode'])throw new RuntimeException('Struktur rujukan Pakej tidak sah.');
            $rows=[];while(($row=fgetcsv($handle,0,',','"',''))!==false){if(count($row)!==2||$row[1]!=='USER_INPUT_KADAR'||!isset($approved[$row[0]])||isset($rows[$row[0]]))throw new RuntimeException('Rekod Pakej tidak sah.');$rows[$row[0]]=$approved[$row[0]];}
            if(array_keys($rows)!==array_keys($approved))throw new RuntimeException('Rujukan Pakej tidak lengkap.');return $rows;
        }finally{fclose($handle);}
    }
    public function validate(array $rows): array
    {
        foreach($rows as &$row){if(is_array($row)&&array_key_exists('kadar',$row))$row['kadar']=SumberWang::normalise($row['kadar']);}unset($row);
        $decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $validator=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,kategori1,tujuan,tarikh_mula,tarikh_tamat,bil_unit,kadar,bil_hari',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1','rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($this->references()))],
            'rekod.*.tujuan'=>'nullable|string|max:500',
            'rekod.*.tarikh_mula'=>'bail|nullable|string|date_format:Y-m-d','rekod.*.tarikh_tamat'=>'bail|nullable|string|date_format:Y-m-d',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kadar'=>$decimal,'rekod.*.bil_hari'=>$decimal,
        ],['in'=>'Pilih nilai rujukan yang sah.','date_format'=>'Gunakan tarikh yang sah.','regex'=>'Gunakan nombor perpuluhan biasa.','array'=>'Struktur baris tidak sah.']);
        $validator->after(function($validator) use($rows){
            foreach($rows as $i=>$row){if(!is_array($row))continue;
                foreach(['tarikh_mula','tarikh_tamat'] as $field){$key='rekod.'.$i.'.'.$field;$value=$row[$field] ?? null;if(is_string($value)&&$value!==''&&!$validator->errors()->has($key)&&substr($value,0,4)!==sprintf('%04d',$this->year))$validator->errors()->add($key,'Tarikh mesti dalam tahun Kitaran.');}
                if(!empty($row['tarikh_mula'])&&!empty($row['tarikh_tamat'])&&!$validator->errors()->has('rekod.'.$i.'.tarikh_mula')&&!$validator->errors()->has('rekod.'.$i.'.tarikh_tamat')&&$row['tarikh_tamat']<$row['tarikh_mula'])$validator->errors()->add('rekod.'.$i.'.tarikh_tamat','Tarikh Tamat tidak boleh mendahului Tarikh Mula.');
            }
        });return $validator->validate()['rekod'];
    }
    public function source(array $row): stdClass
    {
        $data=['definition'=>self::VERSION];foreach(['kategori1','tujuan','tarikh_mula','tarikh_tamat','bil_unit','kadar','bil_hari'] as $key)$data[$key]=$row[$key] ?? null;return (object)$data;
    }
    public function readable(mixed $source): bool
    {
        $keys=['definition','kategori1','tujuan','tarikh_mula','tarikh_tamat','bil_unit','kadar','bil_hari'];
        if(!$source instanceof stdClass||($source->definition ?? null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        if(SumberWang::normalise($source->kadar)!==$source->kadar)return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;
        try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function duration(stdClass $source): ?int
    {
        $start=$source->tarikh_mula ?? null;$end=$source->tarikh_tamat ?? null;
        if(!is_string($start)||!is_string($end)||$start===''||$end==='')return null;
        $a=DateTimeImmutable::createFromFormat('!Y-m-d',$start);$b=DateTimeImmutable::createFromFormat('!Y-m-d',$end);
        if(!$a||!$b||$a->format('Y-m-d')!==$start||$b->format('Y-m-d')!==$end||$b<$a)return null;
        return $a->diff($b)->days+1;
    }
    public function project(stdClass $source): array {return parent::project($source)+['tempoh'=>$this->duration($source)];}
    public function preview(stdClass $source): ?string
    {
        if(!isset($this->references()[$source->kategori1 ?? '']))return null;$factors=[$source->bil_unit ?? null,$source->kadar ?? null,$source->bil_hari ?? null];
        if(in_array(null,$factors,true)||in_array('',$factors,true))return null;$value=BigDecimal::of('1');foreach($factors as $factor)$value=$value->multipliedBy($factor);
        $text=(string)$value;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
