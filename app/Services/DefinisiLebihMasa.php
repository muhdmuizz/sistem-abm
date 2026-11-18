<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** OS14000 source editor; final-row preview only, no generated financial output. */
class DefinisiLebihMasa extends DefinisiAnggaran
{
    public const VERSION='os14000.v1';
    public const PURPOSES=['Kerja-kerja jangka masa terhad','Kerja-kerja mustahak yang tidak boleh ditangguh'];
    private const FIELDS=['tujuan','nama','jawatan','gred','tarikh_mula','tarikh_tamat','jam','bil_hari','kekerapan','bil_bulan'];
    public function __construct(private int $year, private array $programmes=[]) {}
    public function supports(RujukanOs $os): bool {return $os->kod_objek==='14000'&&$os->butiran_perbelanjaan==='Bayaran Lebih Masa'&&!$os->dikecualikan;}
    public function selector(): string {return 'tujuan';}
    public function inputHelp(): string {return 'Satu baris bagi setiap pegawai dan tujuan. Anggaran Setahun = Kadar sejam × Jam Sehari × Hari Setiap Kejadian × Kekerapan Sebulan × Bilangan Bulan. Tarikh tidak menetapkan hari atau bulan yang dikira. Kadar tidak dibundarkan semasa pengiraan; jumlah akhir dibundarkan kepada dua tempat perpuluhan.';}
    public function salaries(): array
    {
        $h=fopen(base_path('docs/references/jawatan-gred-gaji-kgt.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan gaji tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['KLASIFIKASI PERKHIDMATAN','SKIM PERKHIDMATAN/ JAWATAN','SSPA','GAJI MINIMUM','GAJI MAKSIMUM','GAJI MEDIAN','KGT'])throw new RuntimeException('Struktur rujukan gaji tidak sah.');
            $refs=[];$count=0;while(($r=fgetcsv($h,0,',','"',''))!==false){if(count($r)!==7||$r[1]===''||$r[2]===''||isset($refs[$r[1]][$r[2]])||!preg_match('/^\d+(?:\.\d+)?$/D',$r[5]))throw new RuntimeException('Rekod gaji tidak sah.');$refs[$r[1]][$r[2]]=['median'=>$r[5]];$count++;}
            if($count!==131)throw new RuntimeException('Rujukan gaji tidak lengkap.');return $refs;
        }finally{fclose($h);}
    }
    public function references(): array
    {
        $refs=[];$rates=$this->salaries();foreach(self::PURPOSES as $purpose)$refs[$purpose]=['ratesByJob'=>$rates];
        foreach($this->programmes as $key=>$programme)$refs[$key]=['ratesByJob'=>$rates,'place'=>$programme['place']];return $refs;
    }
    public function columns(): array
    {
        $jobs=app(RujukanJawatan::class)->grades();return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Program/ Aktiviti/ Tujuan','type'=>'select','options'=>'references','refreshOnSelect'=>true,'strictValues'=>true,'width'=>330],
            ['key'=>'nama','label'=>'Nama','type'=>'text','maxLength'=>255,'width'=>230],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>array_keys($jobs),'strictValues'=>true,'refreshOnSelect'=>true,'width'=>280],
            ['key'=>'gred','label'=>'Gred','type'=>'select','dependsOn'=>'jawatan','valuesByParent'=>$jobs,'width'=>120],
            ['key'=>'tarikh_mula','label'=>'Tarikh Mula','type'=>'date','min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'width'=>180],
            ['key'=>'tarikh_tamat','label'=>'Tarikh Tamat','type'=>'date','min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'width'=>180],
            ['key'=>'tempoh','label'=>'Tempoh (Hari)','type'=>'output','formula'=>['operation'=>'inclusiveDays','inputs'=>['tarikh_mula','tarikh_tamat']],'width'=>140],
            ['key'=>'kadar','label'=>'Kadar Sejam (RM)','type'=>'output','money'=>true,'numeric'=>true,'formula'=>['operation'=>'overtimeRate'],'width'=>170],
            ['key'=>'jam','label'=>'Jam Sehari','type'=>'decimal','width'=>140],
            ['key'=>'bil_hari','label'=>'Hari Setiap Kejadian','type'=>'decimal','width'=>170],
            ['key'=>'kekerapan','label'=>'Kekerapan Sebulan','type'=>'decimal','width'=>170],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>150],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,'formula'=>['operation'=>'overtimeAmount','inputs'=>['jam','bil_hari','kekerapan','bil_bulan']],'width'=>230],
        ];
    }
    public function validate(array $rows): array
    {
        $jobs=app(RujukanJawatan::class)->grades();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $v=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.tujuan'=>['nullable','string',Rule::in(array_keys($this->references()))],'rekod.*.nama'=>'nullable|string|max:255',
            'rekod.*.jawatan'=>['nullable','string',Rule::in(array_keys($jobs))],'rekod.*.gred'=>'nullable|string|max:20',
            'rekod.*.tarikh_mula'=>'bail|nullable|string|date_format:Y-m-d','rekod.*.tarikh_tamat'=>'bail|nullable|string|date_format:Y-m-d',
            'rekod.*.jam'=>$decimal,'rekod.*.bil_hari'=>$decimal,'rekod.*.kekerapan'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ]);
        $v->after(function($v)use($rows,$jobs){foreach($rows as $i=>$r){if(!is_array($r))continue;$p='rekod.'.$i.'.';
            if(is_string($r['gred']??null)&&$r['gred']!==''&&(!is_string($r['jawatan']??null)||!in_array($r['gred'],$jobs[$r['jawatan']]??[],true)))$v->errors()->add($p.'gred','Gred tidak sepadan dengan Jawatan.');
            if(is_string($r['tujuan']??null)&&isset($this->programmes[$r['tujuan']])&&(int)($r['tempat_bertugas_id']??0)!==$this->programmes[$r['tujuan']]['place'])$v->errors()->add($p.'tujuan','Program/ Aktiviti tidak sepadan dengan Tempat Bertugas.');
            foreach(['tarikh_mula','tarikh_tamat'] as $key)if(is_string($r[$key]??null)&&$r[$key]!==''&&!$v->errors()->has($p.$key)&&substr($r[$key],0,4)!==sprintf('%04d',$this->year))$v->errors()->add($p.$key,'Tarikh mesti dalam tahun Kitaran.');
            if(!empty($r['tarikh_mula'])&&!empty($r['tarikh_tamat'])&&!$v->errors()->has($p.'tarikh_mula')&&!$v->errors()->has($p.'tarikh_tamat')&&$r['tarikh_tamat']<$r['tarikh_mula'])$v->errors()->add($p.'tarikh_tamat','Tarikh Tamat tidak boleh mendahului Tarikh Mula.');
        }});return $v->validate()['rekod'];
    }
    public function source(array $r): stdClass {$s=['definition'=>self::VERSION];foreach(self::FIELDS as $key)$s[$key]=$r[$key]??null;return (object)$s;}
    public function readable(mixed $s): bool
    {
        $keys=['definition',...self::FIELDS];if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($s)))||array_diff(array_keys(get_object_vars($s)),$keys))return false;
        $r=get_object_vars($s);unset($r['definition']);$r['tempat_bertugas_id']=isset($this->programmes[$s->tujuan??''])?$this->programmes[$s->tujuan]['place']:1;try{$this->validate([$r]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    private function median(stdClass $s): ?string {return $this->salaries()[$s->jawatan??''][$s->gred??'']['median']??null;}
    public function project(stdClass $s): array
    {
        $m=$this->median($s);return parent::project($s)+['kadar'=>$m===null?null:(string)BigDecimal::of($m)->multipliedBy(12)->dividedBy(2504,2,RoundingMode::HALF_UP),'tempoh'=>$this->duration($s->tarikh_mula,$s->tarikh_tamat)];
    }
    public function duration(?string $a,?string $b): ?string
    {
        if(!$a||!$b||$b<$a)return null;$start=\DateTimeImmutable::createFromFormat('!Y-m-d',$a);$end=\DateTimeImmutable::createFromFormat('!Y-m-d',$b);return $start&&$end?(string)($start->diff($end)->days+1):null;
    }
    public function preview(stdClass $s): ?string
    {
        $median=$this->median($s);if($median===null||trim($s->nama??'')===''||!isset($this->references()[$s->tujuan??'']))return null;
        $n=BigDecimal::of($median)->multipliedBy(12);foreach(['jam','bil_hari','kekerapan','bil_bulan'] as $k){if(($s->$k??null)===null||$s->$k==='')return null;$n=$n->multipliedBy($s->$k);}
        // Multiply numerator exactly before the sole division/rounding; repeating hourly fractions are never truncated.
        return (string)$n->dividedBy(2504,2,RoundingMode::HALF_UP);
    }
}
