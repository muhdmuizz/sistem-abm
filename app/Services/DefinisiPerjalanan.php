<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use stdClass;

/** Three separate travel variants share source references, never financial identities. */
class DefinisiPerjalanan extends DefinisiAnggaran
{
    public const VERSION='os21000.perjalanan.v1';
    public const COMBINED='Elaun Perjalanan, Bayaran Tambang Bas dan Teksi';
    public const VEHICLE='Elaun Perjalanan Kenderaan';
    public const TAXI='Bas/ Teksi';
    public const MEALS='Makanan dan Minuman';
    public const HOTEL='Penginapan';
    public const LABELS=['Perjalanan Sara Hidup (Umum)','Perjalanan Sara Hidup (Sukan)','Perjalanan Sara Hidup (Kokurikulum)'];
    private const FIELDS=['tujuan','nama','jawatan','gred','tarikh_mula','tarikh_tamat','kategori1','kategori2','lokasi','kadar','jarak','bil_hari','kekerapan','bil_bulan'];
    public function __construct(private int $year) {}
    public function supports(RujukanOs $os): bool {return $os->kod_objek==='21000'&&in_array($os->butiran_perbelanjaan,self::LABELS,true)&&!$os->dikecualikan;}
    public function childSelector(): string {return 'kategori2';}
    public function inputHelp(): string {return 'Satu pegawai dan satu komponen tuntutan bagi setiap baris. Kekerapan ialah perjalanan sebulan. Hari tuntutan, malam penginapan dan bulan diisi sendiri; Tempoh ialah hari kalendar termasuk tarikh akhir dan tidak menetapkannya. Jarak hanya untuk kenderaan. Tambang diisi sendiri; kadar makanan, penginapan dan kenderaan mengikut rujukan. Gred C belum dipadankan.';}
    public function columns(): array
    {
        $jobs=app(RujukanJawatan::class)->grades();return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Program/ Aktiviti/ Tujuan','type'=>'text','maxLength'=>1000,'width'=>280],
            ['key'=>'nama','label'=>'Nama','type'=>'text','maxLength'=>255,'width'=>230],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>array_keys($jobs),'strictValues'=>true,'width'=>280],
            ['key'=>'gred','label'=>'Gred','type'=>'select','dependsOn'=>'jawatan','valuesByParent'=>$jobs,'width'=>120],
            ['key'=>'tarikh_mula','label'=>'Tarikh Mula','type'=>'date','min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'width'=>180],
            ['key'=>'tarikh_tamat','label'=>'Tarikh Tamat','type'=>'date','min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'width'=>180],
            ['key'=>'tempoh','label'=>'Tempoh (Hari)','type'=>'output','formula'=>['operation'=>'inclusiveDays','inputs'=>['tarikh_mula','tarikh_tamat']],'width'=>140],
            ['key'=>'kategori1','label'=>'Kategori1 — Kategori Tuntutan','type'=>'select','options'=>'references','width'=>350],
            ['key'=>'kategori2','label'=>'Kategori2 — Komponen Tuntutan','type'=>'select','dependsOn'=>'kategori1','activeWhen'=>['field'=>'kategori1','value'=>self::COMBINED],'width'=>280],
            ['key'=>'lokasi','label'=>'Lokasi Urusan Rasmi','type'=>'select','dependsOn'=>'kategori2','referenceParents'=>['kategori1','kategori2'],'referenceLeaf'=>true,'width'=>300],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'mixed'=>true,'activeWhenReference'=>'matched','hintReference'=>'basis','resetOn'=>['kategori1','kategori2','lokasi','jawatan','gred'],'width'=>250],
            ['key'=>'jarak','label'=>'Jarak Setiap Perjalanan','type'=>'decimal','activeWhenReference'=>'distance','width'=>190],
            ['key'=>'bil_hari','label'=>'Bilangan Hari/ Malam','type'=>'decimal','activeWhenReference'=>'days','hintReference'=>'quantity_help','labelReference'=>'quantity_label','width'=>190],
            ['key'=>'kekerapan','label'=>'Kekerapan Sebulan','type'=>'decimal','width'=>170],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>150],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,'money'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'requiresMatched'=>true,'requiresPersonnel'=>true,'inputs'=>['kadar','kekerapan','bil_bulan'],'inputsReference'=>'factors'],'width'=>230],
        ];
    }
    public function rateRows(): array {return (new DefinisiPertukaran)->rateRows();}
    public function references(): array
    {
        $grades=array_unique(array_merge(...array_values(app(RujukanJawatan::class)->grades())));$refs=[];
        foreach($this->rateRows() as $r){$components=$r[1]===self::COMBINED?[self::VEHICLE,self::TAXI]:[''];
            foreach($components as $component){$distance=$component===self::VEHICLE;$days=in_array($r[1],[self::MEALS,self::HOTEL],true);$manual=$component===self::TAXI||$r[3]==='';
                $factors=['kadar'];if($distance)$factors[]='jarak';if($days)$factors[]='bil_hari';$factors[]='kekerapan';$factors[]='bil_bulan';
                $ref=&$refs[$r[1]][$component][$r[2]];$ref??=['mode'=>'NO_MATCH','kadar'=>null,'matched'=>false,'distance'=>$distance,'days'=>$days,'personnel'=>true,'factors'=>$factors,'quantity_label'=>$r[1]===self::HOTEL?'Bilangan Malam Setiap Perjalanan':'Hari Tuntutan Setiap Perjalanan','quantity_help'=>$days?($r[1]===self::HOTEL?'Malam penginapan setiap perjalanan; bebas daripada Tempoh.':'Hari yang dituntut setiap perjalanan; bebas daripada Tempoh.') : '','basis'=>'Tiada padanan kadar bagi gred yang dipilih.','ratesByGrade'=>[]];
                foreach($grades as $grade){$level=$grade!=='VU7'&&preg_match('/^[A-Z]+([1-9]|1[0-5])$/D',$grade,$m)?$m[1]:null;
                    if($r[0]!==''&&($level===null||$r[0]!==$level))continue;
                    $ref['ratesByGrade'][$grade]=['mode'=>$manual?'USER_INPUT_KADAR':'FIXED_REFERENCE','kadar'=>$manual?null:$r[3],'matched'=>true,'basis'=>$manual?'Isi tambang bagi setiap perjalanan.':($distance?'Kadar rujukan × Jarak setiap perjalanan.':($r[1]===self::HOTEL?'Kadar rujukan semalam.':'Kadar rujukan sehari.'))];
                }unset($ref);
            }
        }return $refs;
    }
    public function rowReference(array $row): ?array
    {
        if(!is_string($row['kategori1']??null)||!is_string($row['lokasi']??null))return null;
        $ref=$this->references()[$row['kategori1']][$row['kategori2']??''][$row['lokasi']]??null;if(!$ref)return null;
        $jobs=app(RujukanJawatan::class)->grades();$valid=is_string($row['jawatan']??null)&&in_array($row['gred']??null,$jobs[$row['jawatan']]??[],true);return array_replace($ref,$valid?($ref['ratesByGrade'][$row['gred']]??[]):[]);
    }
    public function validate(array $rows): array
    {
        foreach($rows as &$r)if(is_array($r)&&array_key_exists('kadar',$r))$r['kadar']=SumberWang::normalise($r['kadar']);unset($r);
        $jobs=app(RujukanJawatan::class)->grades();$refs=$this->references();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $rules=['rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.tujuan'=>'nullable|string|max:1000','rekod.*.nama'=>'nullable|string|max:255','rekod.*.jawatan'=>['nullable','string',Rule::in(array_keys($jobs))],'rekod.*.gred'=>'nullable|string|max:20','rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($refs))],'rekod.*.kategori2'=>'nullable|string|max:255','rekod.*.lokasi'=>'nullable|string|max:255','rekod.*.tarikh_mula'=>'bail|nullable|string|date_format:Y-m-d','rekod.*.tarikh_tamat'=>'bail|nullable|string|date_format:Y-m-d'];
        foreach(['kadar','jarak','bil_hari','kekerapan','bil_bulan'] as $k)$rules['rekod.*.'.$k]=$decimal;
        $v=Validator::make(['rekod'=>$rows],$rules);
        $v->after(function($v)use($rows,$refs,$jobs){foreach($rows as $i=>$r){if(!is_array($r))continue;$p='rekod.'.$i.'.';
            if(is_string($r['gred']??null)&&$r['gred']!==''&&(!is_string($r['jawatan']??null)||!in_array($r['gred'],$jobs[$r['jawatan']]??[],true)))$v->errors()->add($p.'gred','Gred tidak sepadan dengan Jawatan.');
            $cat=$r['kategori1']??null;$component=$r['kategori2']??null;$location=$r['lokasi']??null;
            if(is_string($component)&&$component!==''&&($cat!==self::COMBINED||!in_array($component,[self::VEHICLE,self::TAXI],true)))$v->errors()->add($p.'kategori2','Komponen hanya terpakai bagi kategori perjalanan kenderaan, bas dan teksi.');
            if(is_string($location)&&$location!==''&&(!is_string($cat)||!is_string($component??'')||!isset($refs[$cat][$component??''][$location])))$v->errors()->add($p.'lokasi','Lokasi tidak sepadan dengan kategori dan komponen.');
            $validShape=is_string($cat??'')&&is_string($component??'')&&is_string($location??'')&&is_string($r['jawatan']??'')&&is_string($r['gred']??'');$ref=$validShape?$this->rowReference($r):null;
            if(($r['kadar']??null)!==null&&$r['kadar']!==''&&(!$ref||!$ref['matched']||$ref['mode']!=='USER_INPUT_KADAR'))$v->errors()->add($p.'kadar','Kadar hanya boleh diisi bagi tambang yang dibenarkan.');
            if(($r['jarak']??null)!==null&&$r['jarak']!==''&&!($ref['distance']??false))$v->errors()->add($p.'jarak','Jarak hanya untuk Elaun Perjalanan Kenderaan.');
            if(($r['bil_hari']??null)!==null&&$r['bil_hari']!==''&&!($ref['days']??false))$v->errors()->add($p.'bil_hari','Hari/ Malam hanya untuk makanan atau penginapan.');
            foreach(['tarikh_mula','tarikh_tamat'] as $k)if(is_string($r[$k]??null)&&$r[$k]!==''&&!$v->errors()->has($p.$k)&&substr($r[$k],0,4)!==sprintf('%04d',$this->year))$v->errors()->add($p.$k,'Tarikh mesti dalam tahun Kitaran.');
            if(!empty($r['tarikh_mula'])&&!empty($r['tarikh_tamat'])&&!$v->errors()->has($p.'tarikh_mula')&&!$v->errors()->has($p.'tarikh_tamat')&&$r['tarikh_tamat']<$r['tarikh_mula'])$v->errors()->add($p.'tarikh_tamat','Tarikh Tamat tidak boleh mendahului Tarikh Mula.');
        }});return $v->validate()['rekod'];
    }
    public function source(array $row): stdClass {$s=['definition'=>self::VERSION];foreach(self::FIELDS as $k)$s[$k]=$row[$k]??null;return (object)$s;}
    public function readable(mixed $s): bool
    {
        $keys=['definition',...self::FIELDS];if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($s)))||array_diff(array_keys(get_object_vars($s)),$keys)||SumberWang::normalise($s->kadar)!==$s->kadar)return false;
        $r=get_object_vars($s);unset($r['definition']);$r['tempat_bertugas_id']=1;try{$this->validate([$r]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function project(stdClass $s): array {return parent::project($s)+['tempoh'=>(new DefinisiLebihMasa($this->year))->duration($s->tarikh_mula,$s->tarikh_tamat)];}
    public function preview(stdClass $s): ?string
    {
        $ref=$this->rowReference(get_object_vars($s));if(!$ref||!$ref['matched']||trim($s->nama??'')==='')return null;
        $r=get_object_vars($s);$r['kadar']=$ref['mode']==='FIXED_REFERENCE'?$ref['kadar']:$s->kadar;$n=BigDecimal::of('1');foreach($ref['factors'] as $key){if(($r[$key]??null)===null||$r[$key]==='')return null;$n=$n->multipliedBy($r[$key]);}
        $value=(string)$n;return str_contains($value,'.')?rtrim(rtrim($value,'0'),'.'):$value;
    }
}
