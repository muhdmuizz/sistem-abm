<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** One OS22000 identity; Kategori1 alone selects its two approved paths. */
class DefinisiPengangkutan extends DefinisiAnggaran
{
    public const VERSION='os22000.v1';
    public const LABEL='Tuntutan Perbelanjaan Pertukaran/ Penempatan Baharu/ Penempatan Semula';
    public const TRANSFER='Pertukaran/ Penempatan';
    public const GOODS='Pengangkutan Barang';
    private const FIELDS=['kategori1','kategori2','kategori3','lokasi','tujuan','nama','jawatan','gred','kadar'];

    public function supports(RujukanOs $os): bool {return $os->kod_objek==='22000'&&$os->butiran_perbelanjaan===self::LABEL&&!$os->dikecualikan;}
    public function childSelector(): string {return 'kategori2';}
    public function inputHelp(): string {return 'Kategori1 menentukan laluan. Pertukaran/ Penempatan menggunakan Lokasi Pertukaran, status keluarga dan gred. Pengangkutan Barang memerlukan Tujuan dan Kadar sahaja. Jumlah = Kadar × 1; tiada kuantiti atau jarak. Kadar yang tiada padanan gred tidak diandaikan.';}

    public function columns(): array
    {
        $jobs=app(RujukanJawatan::class)->grades();$transfer=['field'=>'kategori1','value'=>self::TRANSFER];
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Laluan','type'=>'select','options'=>'references','width'=>250],
            ['key'=>'tujuan','label'=>'Tujuan','type'=>'text','maxLength'=>1000,'activeWhen'=>['field'=>'kategori1','value'=>self::GOODS],'width'=>270],
            ['key'=>'nama','label'=>'Nama','type'=>'text','maxLength'=>255,'activeWhen'=>$transfer,'width'=>220],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>array_keys($jobs),'strictValues'=>true,'activeWhen'=>$transfer,'width'=>280],
            ['key'=>'gred','label'=>'Gred','type'=>'select','dependsOn'=>'jawatan','valuesByParent'=>$jobs,'activeWhen'=>$transfer,'width'=>120],
            ['key'=>'kategori2','label'=>'Kategori2 — Status Keluarga','type'=>'select','dependsOn'=>'kategori1','activeWhen'=>$transfer,'width'=>200],
            ['key'=>'kategori3','label'=>'Kategori3 — Jenis Tuntutan','type'=>'select','dependsOn'=>'kategori2','referenceParents'=>['kategori1','kategori2'],'referenceLeaf'=>true,'activeWhen'=>$transfer,'width'=>260],
            ['key'=>'lokasi','label'=>'Lokasi Pertukaran','type'=>'select','dependsOn'=>'kategori3','referenceParents'=>['kategori1','kategori2','kategori3'],'referenceLeaf'=>true,'activeWhen'=>$transfer,'width'=>280],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'mixed'=>true,'activeWhenReference'=>'matched','hintReference'=>'basis','resetOn'=>['kategori1','kategori2','kategori3','lokasi','jawatan','gred'],'width'=>230],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,'money'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'requiresMatched'=>true,'requiresPersonnel'=>true,'requiresPurpose'=>true,'inputs'=>['kadar']],'width'=>230],
        ];
    }

    public function rateRows(): array
    {
        $h=fopen(base_path('docs/references/OS22000-pengangkutan-barang-kadar.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan OS22000 tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Lokasi Pertukaran','Bujang/ Keluarga','Gred','Kategori Tuntutan','Kadar (RM)'])throw new RuntimeException('Struktur rujukan OS22000 tidak sah.');
            $rows=[];$seen=[];while(($r=fgetcsv($h,0,',','"',''))!==false){
                if(count($r)!==5||!in_array($r[0],['Semenanjung','Sabah/ Sarawak/ WP Labuan'],true)||!in_array($r[1],['Bujang','Keluarga'],true)||!in_array($r[3],['Tambang Pengangkutan Barang','Pemberian Pindahan','Elaun Berpindah','Belanja Pelbagai'],true)||($r[4]!==''&&!preg_match('/^\d+\.\d{2}$/D',$r[4])))throw new RuntimeException('Rekod rujukan OS22000 tidak sah.');
                $key=implode('|',array_slice($r,0,4));if(isset($seen[$key]))throw new RuntimeException('Padanan OS22000 berulang.');$seen[$key]=true;$rows[]=$r;
            }
            if(count($rows)!==126)throw new RuntimeException('Rujukan OS22000 tidak lengkap.');return $rows;
        } finally {fclose($h);}
    }

    public function references(): array
    {
        $refs=[self::TRANSFER=>[],self::GOODS=>[''=>[''=>[''=>['mode'=>'USER_INPUT_KADAR','kadar'=>null,'matched'=>true,'personnel'=>false,'requires_purpose'=>true,'basis'=>'Isi Tujuan dan Kadar; jumlah = Kadar × 1.']]]]];
        $grades=array_unique(array_merge(...array_values(app(RujukanJawatan::class)->grades())));
        foreach($this->rateRows() as $r){$ref=&$refs[self::TRANSFER][$r[1]][$r[3]][$r[0]];
            $ref??=['mode'=>'NO_MATCH','kadar'=>null,'matched'=>false,'personnel'=>true,'requires_purpose'=>false,'basis'=>'Tiada padanan kadar bagi gred yang dipilih.','ratesByGrade'=>[]];
            foreach($grades as $grade){$level=$grade!=='VU7'&&preg_match('/^[A-Z]+([1-9]|1[0-5])$/D',$grade,$m)?$m[1]:null;
                if($r[2]!==''&&($level===null||$r[2]!==$level))continue;
                $ref['ratesByGrade'][$grade]=['mode'=>$r[4]===''?'USER_INPUT_KADAR':'FIXED_REFERENCE','kadar'=>$r[4]===''?null:$r[4],'matched'=>true,'basis'=>$r[4]===''?'Isi kadar bagi padanan rujukan ini.':'Kadar × 1'];
            }unset($ref);
        }return $refs;
    }

    public function rowReference(array $row): ?array
    {
        $ref=$this->references();foreach(['kategori1','kategori2','kategori3','lokasi'] as $k){$value=$row[$k]??'';if(!is_string($value))return null;$ref=$ref[$value]??null;if($ref===null)return null;}
        if(!isset($ref['ratesByGrade']))return $ref;
        $jobs=app(RujukanJawatan::class)->grades();$valid=is_string($row['jawatan']??null)&&in_array($row['gred']??null,$jobs[$row['jawatan']]??[],true);
        return array_replace($ref,$valid?($ref['ratesByGrade'][$row['gred']]??[]):[]);
    }

    public function validate(array $rows): array
    {
        foreach($rows as &$r)if(is_array($r)&&array_key_exists('kadar',$r))$r['kadar']=SumberWang::normalise($r['kadar']);unset($r);
        $jobs=app(RujukanJawatan::class)->grades();$refs=$this->references();
        $rules=['rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in([self::TRANSFER,self::GOODS])],'rekod.*.kategori2'=>'nullable|string|max:255','rekod.*.kategori3'=>'nullable|string|max:255','rekod.*.lokasi'=>'nullable|string|max:255','rekod.*.nama'=>'nullable|string|max:255','rekod.*.tujuan'=>'nullable|string|max:1000','rekod.*.jawatan'=>['nullable','string',Rule::in(array_keys($jobs))],'rekod.*.gred'=>'nullable|string|max:20','rekod.*.kadar'=>['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D']];
        $v=Validator::make(['rekod'=>$rows],$rules);
        $v->after(function($v)use($rows,$refs,$jobs){foreach($rows as $i=>$r){if(!is_array($r))continue;$path=$r['kategori1']??null;$transfer=$path===self::TRANSFER;$goods=$path===self::GOODS;
            foreach(['tujuan','nama','jawatan','gred','kategori2','kategori3','lokasi'] as $key){$active=$key==='tujuan'?$goods:$transfer;if(!$active&&($r[$key]??null)!==null&&$r[$key]!=='')$v->errors()->add("rekod.$i.$key",'Medan tidak terpakai bagi laluan ini.');}
            if($transfer){$branch=$refs[self::TRANSFER];$missing=false;foreach(['kategori2','kategori3','lokasi'] as $key){$val=$r[$key]??null;if($val===null||$val===''){$missing=true;continue;}if(!is_string($val)){$missing=true;continue;}if($missing||!array_key_exists($val,$branch)){$v->errors()->add("rekod.$i.$key",'Pilihan tidak sepadan dengan rujukan OS22000.');$missing=true;continue;}$branch=$branch[$val];}
                if(is_string($r['gred']??null)&&$r['gred']!==''&&(!is_string($r['jawatan']??null)||!in_array($r['gred'],$jobs[$r['jawatan']]??[],true)))$v->errors()->add("rekod.$i.gred",'Gred tidak sepadan dengan Jawatan.');
            }
            $ref=$this->rowReference($r);if(($r['kadar']??null)!==null&&$r['kadar']!==''&&(!$ref||!$ref['matched']||$ref['mode']!=='USER_INPUT_KADAR'))$v->errors()->add("rekod.$i.kadar",'Kadar hanya boleh diisi pada laluan manual atau padanan rujukan berkadar kosong.');
        }});return $v->validate()['rekod'];
    }

    public function source(array $row): stdClass {$s=['definition'=>self::VERSION];foreach(self::FIELDS as $key)$s[$key]=$row[$key]??null;return (object)$s;}
    public function readable(mixed $s): bool
    {
        $keys=['definition',...self::FIELDS];if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($s)))||array_diff(array_keys(get_object_vars($s)),$keys)||SumberWang::normalise($s->kadar)!==$s->kadar)return false;
        $row=get_object_vars($s);unset($row['definition']);$row['tempat_bertugas_id']=1;try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function preview(stdClass $s): ?string
    {
        $ref=$this->rowReference(get_object_vars($s));if(!$ref||!$ref['matched'])return null;
        if($ref['personnel']&&trim($s->nama??'')==='')return null;if($ref['requires_purpose']&&trim($s->tujuan??'')==='')return null;
        $rate=$ref['mode']==='FIXED_REFERENCE'?$ref['kadar']:$s->kadar;if($rate===null||$rate==='')return null;
        $value=(string)BigDecimal::of($rate)->multipliedBy('1');return str_contains($value,'.')?rtrim(rtrim($value,'0'),'.'):$value;
    }
}
