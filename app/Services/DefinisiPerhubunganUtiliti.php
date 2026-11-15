<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Seven approved utility paths; monthly draft preview, not official totals. */
class DefinisiPerhubunganUtiliti extends DefinisiAnggaran
{
    public const VERSION='os23000.perhubungan-utiliti.v1';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek==='23000' && !$os->dikecualikan && $os->butiran_perbelanjaan==='Perhubungan dan Utiliti';
    }

    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1','type'=>'select','options'=>'references','width'=>160],
            ['key'=>'kategori2','label'=>'Kategori2 — Utiliti','type'=>'select','dependsOn'=>'kategori1','width'=>220],
            ['key'=>'kadar','label'=>'Kadar Bulanan (RM)','type'=>'decimal','money'=>true,'width'=>180],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'output','value'=>'12','numeric'=>true,'width'=>130],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['kadar','bil_bulan']],'width'=>200],
        ];
    }

    public function childSelector(): string { return 'kategori2'; }
    public function inputHelp(): string { return 'Pilih kategori dan utiliti. Kadar ialah anggaran bulanan yang diisi sendiri; Bilangan Bulan kekal 12.'; }

    public function references(): array
    {
        $handle=fopen(base_path('docs/references/OS23000-perhubungan-utiliti-kategori.csv'),'rb');
        if($handle===false)throw new RuntimeException('Rujukan utiliti tidak tersedia.');
        try {
            if(fgetcsv($handle,0,',','"','')!==['Kategori1','Kategori2','Kadar Mode'])throw new RuntimeException('Struktur rujukan utiliti tidak sah.');
            $rows=[];$count=0;
            while(($row=fgetcsv($handle,0,',','"',''))!==false){
                if(count($row)!==3||!in_array($row[0],['Pukal','Bukan Pukal'],true)||$row[1]===''||$row[2]!=='USER_INPUT_KADAR'||isset($rows[$row[0]][$row[1]]))throw new RuntimeException('Rekod utiliti tidak sah.');
                $rows[$row[0]][$row[1]]=['mode'=>'USER_INPUT_KADAR'];$count++;
            }
            if($count!==7||array_keys($rows['Pukal'] ?? [])!==['Air','Elektrik','Telefon/ Faks','Pembetungan']||array_keys($rows['Bukan Pukal'] ?? [])!==['Pos/ Mesin Franking','Bil Telefon','Lain-lain'])throw new RuntimeException('Rujukan utiliti tidak lengkap.');
            return $rows;
        } finally {fclose($handle);}
    }

    public function validate(array $rows): array
    {
        foreach($rows as &$row){if(is_array($row)&&array_key_exists('kadar',$row))$row['kadar']=SumberWang::normalise($row['kadar']);}unset($row);
        $references=$this->references();
        return Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,kategori1,kategori2,kadar',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($references))],
            'rekod.*.kategori2'=>['nullable','string',function($attribute,$value,$fail) use($rows,$references){
                $index=(int)explode('.',$attribute)[1];$parent=$rows[$index]['kategori1'] ?? null;
                if(!is_string($value)||!is_string($parent)||!isset($references[$parent][$value]))$fail('Pilih utiliti di bawah kategori yang sah.');
            }],
            'rekod.*.kadar'=>['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'],
        ],['in'=>'Pilih kategori yang sah.','regex'=>'Gunakan kadar bulanan dalam nombor perpuluhan biasa yang sah.','array'=>'Struktur baris tidak sah.'])->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        return (object)['definition'=>self::VERSION,'kategori1'=>$row['kategori1'] ?? null,'kategori2'=>$row['kategori2'] ?? null,'kadar'=>$row['kadar'] ?? null];
    }

    public function readable(mixed $source): bool
    {
        $keys=['definition','kategori1','kategori2','kadar'];
        if(!$source instanceof stdClass||($source->definition ?? null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        if(SumberWang::normalise($source->kadar)!==$source->kadar)return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;
        try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}
        return true;
    }

    public function preview(stdClass $source): ?string
    {
        if(!isset($this->references()[$source->kategori1 ?? ''][$source->kategori2 ?? ''])||($source->kadar ?? null)===null||$source->kadar==='')return null;
        $text=(string)BigDecimal::of($source->kadar)->multipliedBy('12');
        return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
