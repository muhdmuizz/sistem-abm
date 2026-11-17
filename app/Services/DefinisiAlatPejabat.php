<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Direct-entry baseline only; programme inheritance and new-item proposals are separate. */
class DefinisiAlatPejabat extends DefinisiAnggaran
{
    public const VERSION='os27000.office.v1';
    private const FIELDS=['kategori1','kategori2','bil_unit'];

    public function supports(RujukanOs $os): bool {return $os->kod_objek==='27000'&&$os->butiran_perbelanjaan==='Bekalan Alat Pejabat'&&!$os->dikecualikan;}
    public function childSelector(): string {return 'kategori2';}
    public function inputHelp(): string {return 'Pilih Kategori dan Bekalan. Kadar diisi daripada rujukan; Anggaran Setahun = Bil. Unit × Kadar.';}
    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Kategori','type'=>'select','options'=>'references','width'=>270],
            ['key'=>'kategori2','label'=>'Kategori2 — Bekalan','type'=>'select','dependsOn'=>'kategori1','width'=>350],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>150],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'output','reference'=>'kadar','money'=>true,'numeric'=>true,'width'=>150],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'inputs'=>['bil_unit','kadar']],'width'=>230],
        ];
    }
    public function references(): array
    {
        $h=fopen(base_path('docs/references/OS27000-bekalan-alat-pejabat-kategori-kadar.csv'),'rb');
        if(!$h)throw new RuntimeException('Rujukan Bekalan Alat Pejabat tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Kategori1','Kategori2','Kadar (RM)','Kadar Mode','Source Row Retained'])throw new RuntimeException('Struktur rujukan Bekalan Alat Pejabat tidak sah.');
            $refs=[];$seen=[];$count=0;
            while(($r=fgetcsv($h,0,',','"',''))!==false){
                if(count($r)!==5||!in_array($r[0],['Dakwat/ Toner Mesin Pencetak','Alat Tulis Pejabat','Lain-lain','Kertas'],true)||trim($r[1])===''||isset($refs[$r[0]][$r[1]])||$r[3]!=='AUTO_REFERENCE'||!preg_match('/^\d+\.\d{2}$/D',$r[2])||!ctype_digit($r[4])||isset($seen[$r[4]]))throw new RuntimeException('Rekod rujukan Bekalan Alat Pejabat tidak sah.');
                $seen[$r[4]]=true;$refs[$r[0]][$r[1]]=['kadar'=>$r[2]];$count++;
            }
            if($count!==194||count($refs)!==4)throw new RuntimeException('Rujukan Bekalan Alat Pejabat tidak lengkap.');
            return $refs;
        } finally {fclose($h);}
    }
    public function validate(array $rows): array
    {
        $refs=$this->references();
        $v=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($refs))],'rekod.*.kategori2'=>'nullable|string|max:255',
            'rekod.*.bil_unit'=>['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'],
        ]);
        $v->after(function($v)use($rows,$refs){foreach($rows as $i=>$r){if(!is_array($r))continue;$a=$r['kategori1']??null;$b=$r['kategori2']??null;
            if(is_string($b)&&$b!==''&&(!is_string($a)||!isset($refs[$a][$b])))$v->errors()->add("rekod.$i.kategori2",'Bekalan tidak sepadan dengan Kategori yang dipilih.');
        }});return $v->validate()['rekod'];
    }
    public function source(array $row): stdClass {$s=['definition'=>self::VERSION];foreach(self::FIELDS as $key)$s[$key]=$row[$key]??null;return (object)$s;}
    public function readable(mixed $s): bool
    {
        $keys=['definition',...self::FIELDS];if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($s)))||array_diff(array_keys(get_object_vars($s)),$keys))return false;
        $r=get_object_vars($s);unset($r['definition']);$r['tempat_bertugas_id']=1;try{$this->validate([$r]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function preview(stdClass $s): ?string
    {
        $ref=$this->references()[$s->kategori1??''][$s->kategori2??'']??null;
        if(!$ref||($s->bil_unit??null)===null||$s->bil_unit==='')return null;
        $value=(string)BigDecimal::of($s->bil_unit)->multipliedBy($ref['kadar']);return str_contains($value,'.')?rtrim(rtrim($value,'0'),'.'):$value;
    }
}
