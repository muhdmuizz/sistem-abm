<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Source-only selectors choose approved rate components; they add no export columns. */
class DefinisiPertukaran extends DefinisiAnggaran
{
    public const VERSION = 'os21000.pertukaran.v1';
    public const PURPOSE = 'Bertukar Ke Luar Kawasan Pejabat';
    private const FIELDS = ['nama','jawatan','gred','kategori1','kategori2','kategori3','komponen','padanan','kadar','jarak'];
    private const TRAVEL = 'Elaun Perjalanan Kenderaan/ Tambang Pengangkutan Awam';
    private const COMBINED = 'Elaun Perjalanan, Bayaran Tambang Bas dan Teksi';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek === '21000' && $os->butiran_perbelanjaan === 'Tuntutan Perbelanjaan Pertukaran' && !$os->dikecualikan;
    }

    public function childSelector(): string { return 'kategori2'; }
    public function inputHelp(): string
    {
        return 'Pilih Komponen Tuntutan dan Padanan Kadar daripada rujukan. Jarak hanya untuk Elaun Perjalanan Kenderaan. Kadar rujukan diisi automatik; kadar kosong dalam rujukan diisi sendiri. Padanan gred C belum ditetapkan; tiada kadar diandaikan.';
    }

    public function columns(): array
    {
        $jobs = app(RujukanJawatan::class)->grades();
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Tujuan','type'=>'output','value'=>self::PURPOSE,'width'=>270],
            ['key'=>'nama','label'=>'Nama','type'=>'text','maxLength'=>255,'width'=>230],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>array_keys($jobs),'strictValues'=>true,'width'=>280],
            ['key'=>'gred','label'=>'Gred','type'=>'select','dependsOn'=>'jawatan','valuesByParent'=>$jobs,'width'=>120],
            ['key'=>'kategori1','label'=>'Kategori1 — Perpindahan Rumah','type'=>'select','options'=>'references','width'=>240],
            ['key'=>'kategori2','label'=>'Kategori2 — Status Keluarga','type'=>'select','dependsOn'=>'kategori1','width'=>200],
            ['key'=>'kategori3','label'=>'Kategori3 — Jenis Tuntutan','type'=>'select','dependsOn'=>'kategori2','referenceParents'=>['kategori1','kategori2'],'referenceLeaf'=>true,'width'=>330],
            ['key'=>'komponen','label'=>'Komponen Tuntutan','type'=>'select','dependsOn'=>'kategori3','referenceParents'=>['kategori1','kategori2','kategori3'],'referenceLeaf'=>true,'width'=>300],
            ['key'=>'padanan','label'=>'Padanan Kadar','type'=>'select','dependsOn'=>'komponen','referenceParents'=>['kategori1','kategori2','kategori3','komponen'],'referenceLeaf'=>true,'width'=>280],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'mixed'=>true,'activeWhenReference'=>'matched','hintReference'=>'basis','resetOn'=>['kategori1','kategori2','kategori3','komponen','padanan','jawatan','gred'],'width'=>230],
            ['key'=>'jarak','label'=>'Jarak','type'=>'decimal','activeWhenReference'=>'distance','width'=>130],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,'money'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'requiresMatched'=>true,'requiresPersonnel'=>true,'inputs'=>['kadar'],'factorWhenReference'=>'distance','factor'=>'jarak'],'width'=>230],
        ];
    }

    public function rateRows(): array
    {
        $h=fopen(base_path('docs/references/OS21000-perjalanan-sara-hidup-revised-kadar.csv'),'rb');
        if(!$h)throw new RuntimeException('Rujukan Pertukaran tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Gred (Source)','Kategori Tuntutan','Lokasi Urusan Rasmi','Kadar (Source)'])throw new RuntimeException('Struktur kadar Pertukaran tidak sah.');
            $rows=[];$seen=[];
            while(($r=fgetcsv($h,0,',','"',''))!==false){
                if(count($r)!==4 || ($r[3]!==''&&!preg_match('/^\d+\.\d{2}$/D',$r[3])))throw new RuntimeException('Kadar Pertukaran tidak sah.');
                $key=implode('|',array_slice($r,0,3));if(isset($seen[$key]))throw new RuntimeException('Padanan kadar berulang.');$seen[$key]=true;$rows[]=$r;
            }
            if(count($rows)!==83)throw new RuntimeException('Rujukan Pertukaran tidak lengkap.');
            return $rows;
        } finally { fclose($h); }
    }

    public function references(): array
    {
        $rows=$this->rateRows();$grades=array_unique(array_merge(...array_values(app(RujukanJawatan::class)->grades())));$refs=[];
        $components=[self::TRAVEL=>['Elaun Perjalanan Kenderaan'=>self::COMBINED,'Tambang Pengangkutan Awam'=>self::COMBINED],
            'Elaun Makan'=>['Elaun Makan'=>'Makanan dan Minuman'],
            'Bayaran Sewa Hotel/ Elaun Lojing'=>['Bayaran Sewa Hotel/ Elaun Lojing'=>'Penginapan'],
            'Tambang Perjalanan'=>['Bayaran Tambang Kereta Api'=>'Bayaran Tambang Kereta Api','Bayaran Tambang Kapal/ Bot'=>'Bayaran Tambang Kapal/ Bot','Bayaran Kapal Terbang'=>'Bayaran Kapal Terbang']];
        foreach(['Tidak Berpindah Rumah','Berpindah Rumah'] as $move)foreach(['Bujang','Keluarga'] as $family){
            $claims=$move==='Tidak Berpindah Rumah'?[self::TRAVEL]:['Elaun Makan','Bayaran Sewa Hotel/ Elaun Lojing','Tambang Perjalanan'];
            foreach($claims as $claim)foreach($components[$claim] as $component=>$category){
                foreach($rows as $r){if($r[1]!==$category)continue;$distance=$component==='Elaun Perjalanan Kenderaan';
                    $ref=&$refs[$move][$family][$claim][$component][$r[2]];
                    $ref??=['mode'=>'NO_MATCH','kadar'=>null,'matched'=>false,'distance'=>$distance,'personnel'=>true,'basis'=>'Tiada padanan kadar bagi gred yang dipilih.','ratesByGrade'=>[]];
                    foreach($grades as $grade){
                        // Universal full-SSPA -> numerical-level rule; never map VU7 to source C.
                        $level=$grade!=='VU7'&&preg_match('/^[A-Z]+([1-9]|1[0-5])$/D',$grade,$m)?$m[1]:null;
                        if($r[0]!==''&&($level===null||$r[0]!==$level))continue;
                        $ref['ratesByGrade'][$grade]=['mode'=>$r[3]===''?'USER_INPUT_KADAR':'FIXED_REFERENCE','kadar'=>$r[3]===''?null:$r[3],'matched'=>true,'basis'=>$r[3]===''?'Isi kadar bagi padanan rujukan ini.':($distance?'Kadar × Jarak':'Kadar × 1')];
                    }unset($ref);
                }
            }
        }
        return $refs;
    }

    public function rowReference(array $row): ?array
    {
        $ref=$this->references();foreach(['kategori1','kategori2','kategori3','komponen','padanan'] as $k){if(!is_string($row[$k]??null))return null;$ref=$ref[$row[$k]]??null;if($ref===null)return null;}
        $jobs=app(RujukanJawatan::class)->grades();$valid=is_string($row['jawatan']??null)&&in_array($row['gred']??null,$jobs[$row['jawatan']]??[],true);
        return array_replace($ref,$valid?($ref['ratesByGrade'][$row['gred']]??[]):[]);
    }

    public function validate(array $rows): array
    {
        foreach($rows as &$r)if(is_array($r)&&array_key_exists('kadar',$r))$r['kadar']=SumberWang::normalise($r['kadar']);unset($r);
        $jobs=app(RujukanJawatan::class)->grades();$refs=$this->references();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $rules=['rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.nama'=>'nullable|string|max:255','rekod.*.jawatan'=>['nullable','string',Rule::in(array_keys($jobs))],'rekod.*.gred'=>'nullable|string|max:20','rekod.*.kadar'=>$decimal,'rekod.*.jarak'=>$decimal];
        foreach(['kategori1','kategori2','kategori3','komponen','padanan'] as $key)$rules['rekod.*.'.$key]='nullable|string|max:255';
        $v=Validator::make(['rekod'=>$rows],$rules);
        $v->after(function($v)use($rows,$refs,$jobs){foreach($rows as $i=>$r){if(!is_array($r))continue;$branch=$refs;$missing=false;
            foreach(['kategori1','kategori2','kategori3','komponen','padanan'] as $key){$value=$r[$key]??null;if($value===null||$value===''){$missing=true;continue;}
                if(!is_string($value)){$missing=true;continue;}
                if($missing||!is_array($branch)||!array_key_exists($value,$branch)){$v->errors()->add("rekod.$i.$key",'Pilihan tidak sepadan dengan rujukan tuntutan.');$missing=true;continue;}$branch=$branch[$value];
            }
            if(is_string($r['gred']??null)&&$r['gred']!==''&&(!is_string($r['jawatan']??null)||!in_array($r['gred'],$jobs[$r['jawatan']]??[],true)))$v->errors()->add("rekod.$i.gred",'Gred tidak sepadan dengan Jawatan.');
            $ref=$this->rowReference($r);
            if(($r['kadar']??null)!==null&&$r['kadar']!==''&&(!$ref||!$ref['matched']||$ref['mode']!=='USER_INPUT_KADAR'))$v->errors()->add("rekod.$i.kadar",'Kadar hanya boleh diisi untuk padanan rujukan berkadar kosong.');
            if(($r['jarak']??null)!==null&&$r['jarak']!==''&&!($ref['distance']??false))$v->errors()->add("rekod.$i.jarak",'Jarak hanya terpakai bagi Elaun Perjalanan Kenderaan.');
        }});
        return $v->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        $s=['definition'=>self::VERSION,'tujuan'=>self::PURPOSE];foreach(self::FIELDS as $k)$s[$k]=$row[$k]??null;return (object)$s;
    }

    public function readable(mixed $s): bool
    {
        $keys=['definition','tujuan',...self::FIELDS];if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION||($s->tujuan??null)!==self::PURPOSE||array_diff($keys,array_keys(get_object_vars($s)))||array_diff(array_keys(get_object_vars($s)),$keys)||SumberWang::normalise($s->kadar)!==$s->kadar)return false;
        $row=get_object_vars($s);unset($row['definition'],$row['tujuan']);$row['tempat_bertugas_id']=1;try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }

    public function preview(stdClass $s): ?string
    {
        $ref=$this->rowReference(get_object_vars($s));if(!$ref||!$ref['matched']||!is_string($s->nama??null)||trim($s->nama)==='')return null;
        $rate=$ref['mode']==='FIXED_REFERENCE'?$ref['kadar']:$s->kadar;if($rate===null||$rate==='')return null;
        if($ref['distance']&&(($s->jarak??null)===null||$s->jarak===''))return null;
        $value=(string)BigDecimal::of($rate)->multipliedBy($ref['distance']?$s->jarak:'1');return str_contains($value,'.')?rtrim(rtrim($value,'0'),'.'):$value;
    }
}
