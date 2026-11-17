<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Shared approved references; distinct OS identities and permissions. */
class DefinisiBahanMentah extends DefinisiAnggaran
{
    public const VERSION='os26000.v1';
    public const GENERAL='Bekalan Bahan Mentah dan Bahan-bahan Untuk Penyelenggaraan dan Pembaikan';
    private const FIELDS=['tujuan','kategori1','kategori2','kategori3','bil_unit','kekerapan','bil_bulan'];
    public function __construct(private int $year,private bool $bulk) {}
    public function branch(): string {return $this->bulk?'Pukal':'Bukan Pukal';}
    public function supports(RujukanOs $os): bool {return $os->kod_objek==='26000'&&!$os->dikecualikan&&$os->butiran_perbelanjaan===($this->bulk?'Bahan Mentah Pukal':self::GENERAL);}
    public function childSelector(): string {return 'kategori2';}
    public function inputHelp(): string {return 'Unit dan Kadar diisi daripada rujukan. Label kuantiti mengikut unit bekalan. Kekerapan dan Bilangan Bulan hanya untuk Pukal. Kadar bahan api ialah andaian perancangan bagi kitaran yang diluluskan, bukan harga pam semasa.';}
    public function columns(): array
    {
        $bulk=['field'=>'kategori1','value'=>'Pukal'];
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Nama Program/ Aktiviti/ Tujuan','type'=>'text','maxLength'=>1000,'width'=>280],
            ['key'=>'kategori1','label'=>'Kategori1 — Kategori','type'=>'select','options'=>'references','width'=>180],
            ['key'=>'kategori2','label'=>'Kategori2 — Subkategori','type'=>'select','dependsOn'=>'kategori1','width'=>240],
            ['key'=>'kategori3','label'=>'Kategori3 — Jenis Bekalan','type'=>'select','dependsOn'=>'kategori2','referenceParents'=>['kategori1','kategori2'],'referenceLeaf'=>true,'width'=>300],
            ['key'=>'unit','label'=>'Unit','type'=>'output','reference'=>'unit','width'=>110],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','labelReference'=>'quantity_label','hintReference'=>'quantity_label','width'=>150],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'output','reference'=>'kadar','money'=>true,'numeric'=>true,'width'=>150],
            ['key'=>'basis','label'=>'Asas Kadar','type'=>'output','reference'=>'basis','width'=>250],
            ['key'=>'kekerapan','label'=>'Kekerapan','type'=>'decimal','activeWhen'=>$bulk,'width'=>130],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','activeWhen'=>$bulk,'width'=>140],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,'money'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'inputs'=>['bil_unit','kadar'],'inputsReference'=>'factors'],'width'=>230],
        ];
    }
    public function units(): array
    {
        $h=fopen(base_path('docs/references/OS26000-bahan-mentah-unit.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan Unit OS26000 tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Kategori1','Kategori2','Kategori3','Unit'])throw new RuntimeException('Struktur Unit OS26000 tidak sah.');
            $units=[];while(($r=fgetcsv($h,0,',','"',''))!==false){if(count($r)!==4||!in_array($r[3],['Liter','Unit','Botol','Keping','Beg','Batang','Biji','Tin','Kaki','Meter'],true))throw new RuntimeException('Unit OS26000 tidak sah.');$key=implode('|',array_slice($r,0,3));if(isset($units[$key]))throw new RuntimeException('Unit OS26000 berulang.');$units[$key]=$r[3];}
            if(count($units)!==42)throw new RuntimeException('Unit OS26000 tidak lengkap.');return $units;
        } finally {fclose($h);}
    }
    public function references(): array
    {
        $h=fopen(base_path('docs/references/OS26000-bahan-mentah-kategori-kadar.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan OS26000 tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Kategori1','Kategori2','Kategori3','Kadar Per Liter/ Unit (RM; Perancangan 2027)','Kadar Mode','Asas Kadar'])throw new RuntimeException('Struktur OS26000 tidak sah.');
            $units=$this->units();$refs=[];$seen=[];$counts=['Pukal'=>0,'Bukan Pukal'=>0];$fuelCount=0;
            while(($r=fgetcsv($h,0,',','"',''))!==false){$key=implode('|',array_slice($r,0,3));
                if(count($r)!==6||!isset($counts[$r[0]])||!isset($units[$key])||isset($seen[$key])||$r[4]!=='AUTO_REFERENCE'||!preg_match('/^\d+\.\d{2}$/D',$r[3]))throw new RuntimeException('Rekod OS26000 tidak sah.');
                $seen[$key]=true;$counts[$r[0]]++;$fuel=str_starts_with($r[5],'Anggaran tanpa subsidi 2027');$fuelCount+=(int)$fuel;
                if($fuel&&$r[3]!== (str_contains($r[5],'RON95')?'4.00':'5.00'))throw new RuntimeException('Andaian bahan api tidak sepadan.');
                if($r[0]!==$this->branch())continue;$approved=!$fuel||in_array($this->year,[2027,2028],true);
                $refs[$r[0]][$r[1]][$r[2]]=['unit'=>$units[$key],'quantity_label'=>'Bil. '.$units[$key],'kadar'=>$approved?$r[3]:null,'basis'=>$fuel?($approved?'Kadar perancangan tanpa subsidi '.$this->year:'Kadar bahan api belum ditetapkan bagi kitaran ini.'):$r[5],'fuel'=>$fuel,'factors'=>$this->bulk?['bil_unit','kadar','kekerapan','bil_bulan']:['bil_unit','kadar']];
            }
            if($counts!==['Pukal'=>3,'Bukan Pukal'=>39]||count($seen)!==count($units)||$fuelCount!==5)throw new RuntimeException('Rujukan OS26000 tidak lengkap.');return $refs;
        } finally {fclose($h);}
    }
    public function validate(array $rows): array
    {
        $refs=$this->references();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $v=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.tujuan'=>'nullable|string|max:1000','rekod.*.kategori1'=>['nullable','string',Rule::in([$this->branch()])],'rekod.*.kategori2'=>'nullable|string|max:255','rekod.*.kategori3'=>'nullable|string|max:255',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kekerapan'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ]);
        $v->after(function($v)use($rows,$refs){foreach($rows as $i=>$r){if(!is_array($r))continue;$branch=$refs;$missing=false;
            foreach(['kategori1','kategori2','kategori3'] as $key){$value=$r[$key]??null;if($value===null||$value===''){$missing=true;continue;}if(!is_string($value)){$missing=true;continue;}if($missing||!array_key_exists($value,$branch)){$v->errors()->add("rekod.$i.$key",'Pilihan tidak sepadan dengan rujukan OS26000 yang dibenarkan.');$missing=true;continue;}$branch=$branch[$value];}
            foreach(['kekerapan','bil_bulan'] as $key)if(($r['kategori1']??null)!=='Pukal'&&($r[$key]??null)!==null&&$r[$key]!=='')$v->errors()->add("rekod.$i.$key",'Medan hanya terpakai bagi Pukal.');
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
        $ref=$this->references()[$s->kategori1??''][$s->kategori2??''][$s->kategori3??'']??null;if(!$ref||$ref['kadar']===null)return null;
        $values=['bil_unit'=>$s->bil_unit??null,'kadar'=>$ref['kadar'],'kekerapan'=>$s->kekerapan??null,'bil_bulan'=>$s->bil_bulan??null];$total=BigDecimal::of('1');
        foreach($ref['factors'] as $key){if($values[$key]===null||$values[$key]==='')return null;$total=$total->multipliedBy($values[$key]);}
        $value=(string)$total;return str_contains($value,'.')?rtrim(rtrim($value,'0'),'.'):$value;
    }
}
