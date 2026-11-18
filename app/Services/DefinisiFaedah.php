<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** One named recipient/benefit per source; amount = applicable rate × 1. */
class DefinisiFaedah extends DefinisiAnggaran
{
    public const VERSION='os15000.faedah.v1';
    public const LABEL='Faedah-faedah Kewangan Yang Lain';
    public const FEES='Bayaran Balik Yuran';
    public const TRAVEL='Tambang Ziarah Wilayah';
    public const BIPS='Bayaran Insentif Berasaskan Prestasi (BIPs)';
    public const HPA='Hadiah Pengiktirafan Akademik (HPA)';
    private const FIELDS=['kategori1','kategori2','kategori3','nama','jawatan','gred','tujuan','kadar'];

    public function supports(RujukanOs $os): bool {return $os->kod_objek==='15000'&&$os->butiran_perbelanjaan===self::LABEL&&!$os->dikecualikan;}
    public function childSelector(): string {return 'kategori2';}
    public function inputHelp(): string {return 'Satu baris untuk seorang penerima dan satu kemudahan. Anggaran Setahun = Kadar × 1. Kadar mengikut rujukan kecuali Yuran dan Tambang Ziarah Wilayah. Tujuan Yuran diperlukan bagi Bayaran Balik Yuran. KGT peratus belum tersedia.';}
    public function columns(): array
    {
        $jobs=app(RujukanJawatan::class)->grades();
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Jenis Kemudahan','type'=>'select','options'=>'references','width'=>350],
            ['key'=>'nama','label'=>'Nama','type'=>'text','maxLength'=>255,'width'=>230],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>array_keys($jobs),'strictValues'=>true,'width'=>280],
            ['key'=>'gred','label'=>'Gred','type'=>'select','dependsOn'=>'jawatan','valuesByParent'=>$jobs,'width'=>120],
            ['key'=>'tujuan','label'=>'Tujuan Yuran','type'=>'text','maxLength'=>1000,'activeWhen'=>['field'=>'kategori1','value'=>self::FEES],'width'=>270],
            ['key'=>'kategori2','label'=>'Kategori2 — Wilayah Asal/ Kelayakan','type'=>'select','dependsOn'=>'kategori1','activeWhen'=>['field'=>'kategori1','values'=>[self::TRAVEL,self::HPA]],'width'=>270],
            ['key'=>'kategori3','label'=>'Kategori3 — Wilayah','type'=>'select','dependsOn'=>'kategori2','referenceParents'=>['kategori1','kategori2'],'referenceLeaf'=>true,'activeWhen'=>['field'=>'kategori1','value'=>self::TRAVEL],'width'=>190],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'mixed'=>true,'activeWhenReference'=>'matched','resetOn'=>['kategori1','kategori2','kategori3','jawatan','gred'],'width'=>170],
            ['key'=>'basis','label'=>'Asas Kadar','type'=>'output','reference'=>'basis','width'=>300],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'requiresMatched'=>true,'requiresPersonnel'=>true,'requiresPurpose'=>true,'inputs'=>['kadar']],'width'=>230],
        ];
    }
    public function gradeRates(): array
    {
        $h=fopen(base_path('docs/references/OS15000-kemudahan-gred-kadar.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan kadar Faedah tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Jenis Kemudahan','Gred (Source)','Kadar (RM; Source)'])throw new RuntimeException('Struktur kadar Faedah tidak sah.');
            $rates=[];$count=0;while(($r=fgetcsv($h,0,',','"',''))!==false){if(count($r)!==3||isset($rates[$r[0]][$r[1]])||($r[1]!==''&&!preg_match('/^(?:C|[1-9]|1[0-5])$/D',$r[1]))||($r[2]!==''&&!preg_match('/^\d+\.\d{2}$/D',$r[2])))throw new RuntimeException('Rekod kadar Faedah tidak sah.');$rates[$r[0]][$r[1]]=$r[2];$count++;}
            if($count!==125||count($rates)!==10||($rates[self::FEES]['']??null)!=='')throw new RuntimeException('Rujukan kadar Faedah tidak lengkap.');return $rates;
        } finally {fclose($h);}
    }
    public function kgtRates(): array
    {
        $h=fopen(base_path('docs/references/jawatan-gred-gaji-kgt.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan KGT tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['KLASIFIKASI PERKHIDMATAN','SKIM PERKHIDMATAN/ JAWATAN','SSPA','GAJI MINIMUM','GAJI MAKSIMUM','GAJI MEDIAN','KGT'])throw new RuntimeException('Struktur KGT tidak sah.');
            $rates=[];$count=0;while(($r=fgetcsv($h,0,',','"',''))!==false){if(count($r)!==7||isset($rates[$r[1]][$r[2]])||(!preg_match('/^\d+(?:\.\d+)?$/D',$r[6])&&!($r[1]==='PENGARAH PENDIDIKAN NEGERI'&&$r[2]==='VU7'&&$r[6]==='')))throw new RuntimeException('Rekod KGT tidak sah.');$rates[$r[1]][$r[2]]=$r[6];$count++;}
            if($count!==131)throw new RuntimeException('Rujukan KGT tidak lengkap.');return $rates;
        } finally {fclose($h);}
    }
    private function metadata(string $mode,?string $rate,bool $matched,string $basis,bool $purpose=false): array {return ['mode'=>$mode,'kadar'=>$rate,'matched'=>$matched,'basis'=>$basis,'personnel'=>true,'requires_purpose'=>$purpose];}
    public function references(): array
    {
        $rates=$this->gradeRates();$jobs=app(RujukanJawatan::class)->grades();$allGrades=array_unique(array_merge(...array_values($jobs)));$kgt=$this->kgtRates();
        $h=fopen(base_path('docs/references/OS15000-faedah-faedah-kewangan-yang-lain-kategori.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan Kemudahan tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Kod Objek','Butiran Perbelanjaan','Kategori1','Kategori2','Kategori3','Kadar (RM)','Gred','Kadar Mode','Nota','Rujukan'])throw new RuntimeException('Struktur Kemudahan tidak sah.');
            $refs=[];$count=0;
            while(($r=fgetcsv($h,0,',','"',''))!==false){if(count($r)!==10||$r[0]!=='15000'||$r[1]!==self::LABEL||isset($refs[$r[2]][$r[3]][$r[4]])||$r[6]!=='')throw new RuntimeException('Rekod Kemudahan tidak sah.');
                if($r[7]==='GRADE_RATE_LOOKUP'){
                    if(!isset($rates[$r[2]])||$r[5]!==''||$r[3]!==''||$r[4]!=='')throw new RuntimeException('Padanan Kemudahan tidak sah.');
                    $ref=$this->metadata('NO_MATCH',null,false,'Tiada padanan kadar bagi gred yang dipilih. Gred C tidak diandaikan.');$ref['ratesByGrade']=[];
                    foreach($allGrades as $grade){if($grade==='VU7'||!preg_match('/^[A-Z]+([1-9]|1[0-5])$/D',$grade,$m))continue;$rate=$rates[$r[2]][$m[1]]??null;if($rate===null||$rate==='')continue;$ref['ratesByGrade'][$grade]=['mode'=>'FIXED_REFERENCE','kadar'=>$rate,'matched'=>true,'basis'=>'Kadar rujukan mengikut gred; Kadar × 1.'];}
                }elseif($r[7]==='KGT_REFERENCE'&&$r[2]===self::BIPS){
                    $ref=$this->metadata('NO_MATCH',null,false,'Pilih Jawatan dan Gred untuk padanan KGT.');$ref['ratesByJob']=[];
                    foreach($kgt as $job=>$grades)foreach($grades as $grade=>$rate){$numeric=preg_match('/^\d+(?:\.\d+)?$/D',$rate)===1;$ref['ratesByJob'][$job][$grade]=['mode'=>$numeric?'FIXED_REFERENCE':'UNRESOLVED_PERCENT','kadar'=>$numeric?$rate:null,'matched'=>$numeric,'basis'=>$numeric?'KGT rujukan Jawatan dan Gred; Kadar × 1.':'KGT peratus belum tersedia; asas gaji dan pembundaran belum diputuskan.'];}
                }elseif($r[7]==='FIXED_RATE'&&$r[2]===self::HPA&&preg_match('/^\d+\.\d{2}$/D',$r[5]))$ref=$this->metadata('FIXED_REFERENCE',$r[5],true,'Kadar HPA mengikut kelayakan; Kadar × 1.');
                elseif($r[7]==='USER_INPUT_DESCRIPTION_AND_KADAR'&&$r[2]===self::FEES)$ref=$this->metadata('USER_INPUT_KADAR',null,true,'Isi Tujuan Yuran dan Kadar; Kadar × 1.',true);
                elseif($r[7]==='USER_INPUT_KADAR'&&$r[2]===self::TRAVEL)$ref=$this->metadata('USER_INPUT_KADAR',null,true,'Isi kadar tambang bagi wilayah dipilih; Kadar × 1.');
                else throw new RuntimeException('Kaedah kadar Kemudahan tidak sah.');
                $refs[$r[2]][$r[3]][$r[4]]=$ref;$count++;
            }
            if($count!==17||count($refs)!==13)throw new RuntimeException('Rujukan Kemudahan tidak lengkap.');return $refs;
        } finally {fclose($h);}
    }
    private function personnel(array $row): bool {return is_string($row['jawatan']??null)&&is_string($row['gred']??null)&&in_array($row['gred'],app(RujukanJawatan::class)->grades()[$row['jawatan']]??[],true);}
    public function rowReference(array $row): ?array
    {
        $ref=$this->references();foreach(['kategori1','kategori2','kategori3'] as $key){$value=$row[$key]??'';if(!is_string($value))return null;$ref=$ref[$value]??null;if($ref===null)return null;}
        if(!$this->personnel($row))return $ref;
        if(isset($ref['ratesByJob']))return array_replace($ref,$ref['ratesByJob'][$row['jawatan']][$row['gred']]??[]);
        if(isset($ref['ratesByGrade']))return array_replace($ref,$ref['ratesByGrade'][$row['gred']]??[]);return $ref;
    }
    public function validate(array $rows): array
    {
        foreach($rows as &$r)if(is_array($r)&&array_key_exists('kadar',$r))$r['kadar']=SumberWang::normalise($r['kadar']);unset($r);
        $refs=$this->references();$jobs=app(RujukanJawatan::class)->grades();
        $v=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($refs))],'rekod.*.kategori2'=>'nullable|string|max:255','rekod.*.kategori3'=>'nullable|string|max:255',
            'rekod.*.nama'=>'nullable|string|max:255','rekod.*.jawatan'=>['nullable','string',Rule::in(array_keys($jobs))],'rekod.*.gred'=>'nullable|string|max:20','rekod.*.tujuan'=>'nullable|string|max:1000',
            'rekod.*.kadar'=>['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'],
        ]);
        $v->after(function($v)use($rows,$refs){foreach($rows as $i=>$r){if(!is_array($r))continue;$category=$r['kategori1']??null;$branch=$refs;$missing=false;
            foreach(['kategori1','kategori2','kategori3'] as $key){$value=$r[$key]??'';if(!is_string($value)){$missing=true;continue;}if($missing||!array_key_exists($value,$branch)){if($value!=='')$v->errors()->add("rekod.$i.$key",'Pilihan tidak sepadan dengan Kemudahan.');$missing=true;continue;}$branch=$branch[$value];}
            foreach(['kategori2','kategori3','tujuan'] as $key){$active=$key==='tujuan'?$category===self::FEES:($key==='kategori3'?$category===self::TRAVEL:in_array($category,[self::TRAVEL,self::HPA],true));if(!$active&&($r[$key]??null)!==null&&$r[$key]!=='')$v->errors()->add("rekod.$i.$key",'Medan tidak terpakai bagi Kemudahan ini.');}
            if(is_string($r['gred']??null)&&$r['gred']!==''&&!$this->personnel($r))$v->errors()->add("rekod.$i.gred",'Gred tidak sepadan dengan Jawatan.');
            $ref=$this->rowReference($r);if(($r['kadar']??null)!==null&&$r['kadar']!==''&&(!$ref||!$ref['matched']||$ref['mode']!=='USER_INPUT_KADAR'))$v->errors()->add("rekod.$i.kadar",'Kadar hanya boleh diisi bagi laluan manual yang sah.');
        }});return $v->validate()['rekod'];
    }
    public function source(array $row): stdClass {$s=['definition'=>self::VERSION];foreach(self::FIELDS as $key)$s[$key]=$row[$key]??null;return (object)$s;}
    public function readable(mixed $s): bool
    {
        $keys=['definition',...self::FIELDS];if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($s)))||array_diff(array_keys(get_object_vars($s)),$keys)||SumberWang::normalise($s->kadar)!==$s->kadar)return false;
        $r=get_object_vars($s);unset($r['definition']);$r['tempat_bertugas_id']=1;try{$this->validate([$r]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function preview(stdClass $s): ?string
    {
        $row=get_object_vars($s);$ref=$this->rowReference($row);if(!$ref||!$ref['matched']||!$this->personnel($row)||trim($s->nama??'')===''||($ref['requires_purpose']&&trim($s->tujuan??'')===''))return null;
        $rate=$ref['mode']==='FIXED_REFERENCE'?$ref['kadar']:$s->kadar;if($rate===null||$rate==='')return null;
        $value=(string)BigDecimal::of($rate)->multipliedBy('1');return str_contains($value,'.')?rtrim(rtrim($value,'0'),'.'):$value;
    }
}
