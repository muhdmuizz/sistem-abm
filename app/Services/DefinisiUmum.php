<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

class DefinisiUmum extends DefinisiPakej
{
    public const VERSION='os29000.umum.v1';
    private const FIELDS=['tujuan','tarikh_mula','tarikh_tamat','kategori1','kategori2','nama','jawatan','gred','bil_unit','kadar'];
    public function supports(RujukanOs $os): bool {return $os->kod_objek==='29000'&&!$os->dikecualikan&&$os->butiran_perbelanjaan==='Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti';}
    public function childSelector(): string {return 'kategori2';}
    public function inputHelp(): string {return 'Kadar rujukan diisi automatik. Nama, Jawatan dan Gred diperlukan bagi Penceramah, Fasilitator dan Yuran; draf belum lengkap boleh disimpan. Kuantiti mengikut Unit; kadar Penceramah/ Fasilitator ialah RM sejam.';}
    public function columns(): array
    {
        $dates=collect(parent::columns())->keyBy('key');$grades=app(RujukanJawatan::class)->grades();
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Nama Program/ Aktiviti/ Tujuan','type'=>'text','maxLength'=>500,'width'=>260],
            $dates['tarikh_mula'],$dates['tarikh_tamat'],$dates['tempoh'],
            ['key'=>'kategori1','label'=>'Kategori1 — Kategori','type'=>'select','options'=>'references','width'=>280],
            ['key'=>'kategori2','label'=>'Kategori2 — Perkhidmatan','type'=>'select','dependsOn'=>'kategori1','width'=>260],
            ['key'=>'nama','label'=>'Nama','type'=>'text','maxLength'=>255,'activeWhenReference'=>'personnel','width'=>240],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>array_keys($grades),'activeWhenReference'=>'personnel','width'=>280],
            ['key'=>'gred','label'=>'Gred','type'=>'select','dependsOn'=>'jawatan','valuesByParent'=>$grades,'activeWhenReference'=>'personnel','width'=>150],
            ['key'=>'payment_grade','label'=>'Gred Penceramah/ Fasilitator','type'=>'output','reference'=>'payment_grade','width'=>230],
            ['key'=>'unit','label'=>'Unit','type'=>'output','reference'=>'unit','width'=>100],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','labelReference'=>'quantity_label','hintReference'=>'quantity_label','width'=>150],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'mixed'=>true,'hintReference'=>'basis','width'=>190],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,'money'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'requiresPersonnel'=>true,'inputs'=>['bil_unit','kadar']],'width'=>220],
        ];
    }
    public function classification(?string $grade): ?string
    {
        if($grade==='VU7')return 'Kumpulan Pengurusan Tertinggi';
        if(!is_string($grade)||!preg_match('/^[A-Z]+([1-9]|1[0-4])$/D',$grade,$m))return null;
        $n=(int)$m[1];return $n===14?'Kumpulan P&P Gred 14':($n>=11?'Kumpulan P&P Gred 11-13':($n>=9?'Kumpulan P&P Gred 9-10':'Kumpulan Sokongan'));
    }
    public function references(): array
    {
        $h=fopen(base_path('docs/references/OS29000-umum-kategori-kadar.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan Umum tidak tersedia.');
        try{
            if(fgetcsv($h,0,',','"','')!==['Kategori1','Kategori2','Gred Penceramah/ Fasilitator','Kadar (RM; Reference)','Kadar Mode'])throw new RuntimeException('Struktur Umum tidak sah.');
            $refs=[];$count=0;$fixed=0;
            while(($r=fgetcsv($h,0,',','"',''))!==false){
                if(count($r)!==5||!in_array($r[4],['AUTO_REFERENCE','USER_INPUT_KADAR'],true)||($r[4]==='AUTO_REFERENCE'?!preg_match('/^\d+\.\d{2}$/D',$r[3]):$r[3]!==''))throw new RuntimeException('Rekod Umum tidak sah.');
                $speaker=in_array($r[1],['Bayaran Penceramah','Bayaran Fasilitator'],true);$personnel=$speaker||$r[1]==='Bayaran Yuran';
                $unit=$speaker?'Jam':($r[1]==='Bayaran Yuran'?'Peserta':(str_starts_with($r[1],'Makan Minum')?'Orang':'Unit'));
                $refs[$r[0]][$r[1]]??=['mode'=>$r[4]==='AUTO_REFERENCE'?'FIXED_REFERENCE':'USER_INPUT_KADAR','kadar'=>$speaker||$r[3]===''?null:$r[3],'personnel'=>$personnel,'unit'=>$unit,'quantity_label'=>match($unit){'Jam'=>'Bil. Jam','Peserta'=>'Bil. Peserta','Orang'=>'Bil. Orang',default=>'Bil. Unit'},'basis'=>$speaker?'RM sejam':($unit==='Peserta'?'RM seorang peserta':($unit==='Orang'?'RM seorang':'RM seunit'))];
                if($speaker){if($r[2]===''||isset($refs[$r[0]][$r[1]]['speaker_rates'][$r[2]]))throw new RuntimeException('Padanan Umum berulang.');$refs[$r[0]][$r[1]]['speaker_rates'][$r[2]]=$r[3];}
                elseif($r[2]!=='')throw new RuntimeException('Pengelasan tidak terpakai.');
                $count++;$fixed+=(int)($r[4]==='AUTO_REFERENCE');
            }
            if($count!==22||$fixed!==18||count($refs)!==3)throw new RuntimeException('Rujukan Umum tidak lengkap.');
            foreach($refs as &$services)foreach($services as &$ref)if(isset($ref['speaker_rates'])){
                if(count($ref['speaker_rates'])!==5)throw new RuntimeException('Kadar Penceramah tidak lengkap.');
                $ref['ratesByGrade']=[];foreach(app(RujukanJawatan::class)->grades() as $grades)foreach($grades as $grade){$class=$this->classification($grade);if($class!==null&&isset($ref['speaker_rates'][$class]))$ref['ratesByGrade'][$grade]=['kadar'=>$ref['speaker_rates'][$class],'payment_grade'=>$class];}
            }unset($services,$ref);
            return $refs;
        }finally{fclose($h);}
    }
    public function rowReference(array $row): ?array
    {
        $ref=$this->references()[$row['kategori1']??''][$row['kategori2']??'']??null;if(!$ref)return null;
        if(isset($ref['ratesByGrade'])){
            $valid=in_array($row['gred']??null,app(RujukanJawatan::class)->grades()[$row['jawatan']??'']??[],true);
            $ref=array_replace($ref,$valid?($ref['ratesByGrade'][$row['gred']]??[]):[]);
        }return $ref;
    }
    public function validate(array $rows): array
    {
        foreach($rows as &$row)if(is_array($row)&&array_key_exists('kadar',$row))$row['kadar']=SumberWang::normalise($row['kadar']);unset($row);
        $refs=$this->references();$jobs=app(RujukanJawatan::class)->grades();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $v=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,tujuan,tarikh_mula,tarikh_tamat,kategori1,kategori2,nama,jawatan,gred,bil_unit,kadar',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.tujuan'=>'nullable|string|max:500','rekod.*.tarikh_mula'=>'nullable|string|date_format:Y-m-d','rekod.*.tarikh_tamat'=>'nullable|string|date_format:Y-m-d',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($refs))],'rekod.*.kategori2'=>'nullable|string|max:255',
            'rekod.*.nama'=>'nullable|string|max:255','rekod.*.jawatan'=>['nullable','string',Rule::in(array_keys($jobs))],'rekod.*.gred'=>'nullable|string|max:20',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kadar'=>$decimal,
        ]);
        $v->after(function($v)use($rows,$refs,$jobs){foreach($rows as $i=>$row){if(!is_array($row))continue;
            $parent=$row['kategori1']??null;$child=$row['kategori2']??null;$ref=is_string($parent)&&is_string($child)?($refs[$parent][$child]??null):null;
            if(is_string($child)&&$child!==''&&!$ref)$v->errors()->add("rekod.$i.kategori2",'Perkhidmatan tidak sepadan dengan kategori.');
            foreach(['nama','jawatan','gred'] as $key)if(!($ref['personnel']??false)&&($row[$key]??null)!==null&&$row[$key]!=='')$v->errors()->add("rekod.$i.$key",'Medan tidak terpakai bagi perkhidmatan ini.');
            if(is_string($row['gred']??null)&&$row['gred']!==''&&(!is_string($row['jawatan']??null)||!in_array($row['gred'],$jobs[$row['jawatan']]??[],true)))$v->errors()->add("rekod.$i.gred",'Gred tidak sepadan dengan Jawatan.');
            if($ref&&$ref['mode']==='FIXED_REFERENCE'&&($row['kadar']??null)!==null&&$row['kadar']!=='')$v->errors()->add("rekod.$i.kadar",'Kadar rujukan tidak boleh dipinda.');
            $dateRow=array_intersect_key($row,array_flip(['tempat_bertugas_id','tujuan','tarikh_mula','tarikh_tamat']));
            try{parent::validate([$dateRow]);}catch(\Illuminate\Validation\ValidationException $e){foreach($e->errors() as $key=>$messages)foreach($messages as $message)$v->errors()->add(preg_replace('/^rekod\.0\./','rekod.'.$i.'.',$key),$message);}
        }});return $v->validate()['rekod'];
    }
    public function source(array $row): stdClass {$data=['definition'=>self::VERSION];foreach(self::FIELDS as $key)$data[$key]=$row[$key]??null;return (object)$data;}
    public function readable(mixed $source): bool
    {
        $keys=['definition',...self::FIELDS];if(!$source instanceof stdClass||($source->definition??null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys)||SumberWang::normalise($source->kadar)!==$source->kadar)return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function preview(stdClass $source): ?string
    {
        $ref=$this->rowReference(get_object_vars($source));if(!$ref)return null;
        if($ref['personnel']&&(!$source->nama||!$source->jawatan||!$source->gred))return null;
        $rate=$ref['mode']==='FIXED_REFERENCE'?$ref['kadar']:$source->kadar;
        if($rate===null||$rate===''||$source->bil_unit===null||$source->bil_unit==='')return null;
        $text=(string)BigDecimal::of($source->bil_unit)->multipliedBy($rate);return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
