<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Bayaran Perubatan only; manual factors and independent kidney-contract duration. */
class DefinisiPerubatan extends DefinisiAnggaran
{
    public const VERSION='os15000.perubatan.v1';
    public const KIDNEY='Rawatan Penyakit Buah Pinggang';
    private const FIELDS=['kategori1','nama','jawatan','gred','tarikh_mula','tarikh_tamat','bil_unit','kadar','kekerapan','bil_bulan'];
    public function __construct(private int $year) {}
    public function supports(RujukanOs $os): bool {return $os->kod_objek==='15000'&&!$os->dikecualikan&&$os->butiran_perbelanjaan==='Bayaran Perubatan';}
    public function inputHelp(): string {return 'Kadar diisi sendiri. Anggaran Setahun = Bil. Unit × Kadar × Kekerapan × Bilangan Bulan. Tarikh kontrak hanya untuk Rawatan Penyakit Buah Pinggang; tempoh tidak menetapkan Bilangan Bulan.';}
    public function columns(): array
    {
        $jobs=app(RujukanJawatan::class)->grades();$kidney=['field'=>'kategori1','value'=>self::KIDNEY];
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Jenis Kemudahan Perubatan','type'=>'select','options'=>'references','refreshOnSelect'=>true,'width'=>350],
            ['key'=>'nama','label'=>'Nama','type'=>'text','maxLength'=>255,'width'=>230],
            ['key'=>'jawatan','label'=>'Jawatan','type'=>'select','options'=>'values','values'=>array_keys($jobs),'strictValues'=>true,'refreshOnSelect'=>true,'width'=>280],
            ['key'=>'gred','label'=>'Gred','type'=>'select','dependsOn'=>'jawatan','valuesByParent'=>$jobs,'width'=>120],
            ['key'=>'tarikh_mula','label'=>'Tarikh Mula Kontrak','type'=>'date','activeWhen'=>$kidney,'min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'width'=>190],
            ['key'=>'tarikh_tamat','label'=>'Tarikh Tamat Kontrak','type'=>'date','activeWhen'=>$kidney,'min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'width'=>190],
            ['key'=>'tempoh','label'=>'Tempoh Kontrak','type'=>'output','activeWhen'=>$kidney,'formula'=>['operation'=>'inclusiveCalendar','inputs'=>['tarikh_mula','tarikh_tamat']],'width'=>220],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>140],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'width'=>160],
            ['key'=>'kekerapan','label'=>'Kekerapan','type'=>'decimal','width'=>140],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>150],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,'formula'=>['operation'=>'multiply','requiresReference'=>true,'inputs'=>['bil_unit','kadar','kekerapan','bil_bulan']],'width'=>230],
        ];
    }
    public function references(): array
    {
        $h=fopen(base_path('docs/references/OS15000-bayaran-perubatan-kategori.csv'),'rb');if(!$h)throw new RuntimeException('Rujukan Bayaran Perubatan tidak tersedia.');
        try {
            if(fgetcsv($h,0,',','"','')!==['Kod Objek','Butiran Perbelanjaan','Kod Aktiviti','Kategori1','Kategori2','Kategori3','Kadar (RM)','Kadar Mode'])throw new RuntimeException('Struktur Bayaran Perubatan tidak sah.');
            $refs=[];while(($r=fgetcsv($h,0,',','"',''))!==false){if(count($r)!==8||$r[0]!=='15000'||$r[1]!=='Bayaran Perubatan'||$r[2]!=='010804'||trim($r[3])===''||isset($refs[$r[3]])||array_slice($r,4,3)!==['','','']||$r[7]!=='USER_INPUT_KADAR')throw new RuntimeException('Rekod Bayaran Perubatan tidak sah.');$refs[$r[3]]=['mode'=>'USER_INPUT_KADAR'];}
            if(count($refs)!==9)throw new RuntimeException('Rujukan Bayaran Perubatan tidak lengkap.');return $refs;
        } finally {fclose($h);}
    }
    public function validate(array $rows): array
    {
        $jobs=app(RujukanJawatan::class)->grades();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $v=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0','rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($this->references()))],'rekod.*.nama'=>'nullable|string|max:255',
            'rekod.*.jawatan'=>['nullable','string',Rule::in(array_keys($jobs))],'rekod.*.gred'=>'nullable|string|max:20',
            'rekod.*.tarikh_mula'=>'bail|nullable|string|date_format:Y-m-d','rekod.*.tarikh_tamat'=>'bail|nullable|string|date_format:Y-m-d',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kadar'=>$decimal,'rekod.*.kekerapan'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ]);
        $v->after(function($v)use($rows,$jobs){foreach($rows as $i=>$r){if(!is_array($r))continue;$prefix='rekod.'.$i.'.';
            if(is_string($r['gred']??null)&&$r['gred']!==''&&(!is_string($r['jawatan']??null)||!in_array($r['gred'],$jobs[$r['jawatan']]??[],true)))$v->errors()->add($prefix.'gred','Gred tidak sepadan dengan Jawatan.');
            foreach(['tarikh_mula','tarikh_tamat'] as $key){$value=$r[$key]??null;if($value===null||$value==='')continue;
                if(($r['kategori1']??null)!==self::KIDNEY)$v->errors()->add($prefix.$key,'Tarikh kontrak hanya untuk Rawatan Penyakit Buah Pinggang.');
                elseif(is_string($value)&&!$v->errors()->has($prefix.$key)&&substr($value,0,4)!==sprintf('%04d',$this->year))$v->errors()->add($prefix.$key,'Tarikh mesti dalam tahun Kitaran.');
            }
            if(is_string($r['tarikh_mula']??null)&&is_string($r['tarikh_tamat']??null)&&$r['tarikh_mula']!==''&&$r['tarikh_tamat']!==''&&!$v->errors()->has($prefix.'tarikh_mula')&&!$v->errors()->has($prefix.'tarikh_tamat')&&$r['tarikh_tamat']<$r['tarikh_mula'])$v->errors()->add($prefix.'tarikh_tamat','Tarikh Tamat tidak boleh mendahului Tarikh Mula.');
        }});return $v->validate()['rekod'];
    }
    public function source(array $row): stdClass {$s=['definition'=>self::VERSION];foreach(self::FIELDS as $key)$s[$key]=$row[$key]??null;return (object)$s;}
    public function readable(mixed $s): bool
    {
        $keys=['definition',...self::FIELDS];if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($s)))||array_diff(array_keys(get_object_vars($s)),$keys))return false;
        $r=get_object_vars($s);unset($r['definition']);$r['tempat_bertugas_id']=1;try{$this->validate([$r]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function project(stdClass $s): array {return parent::project($s)+['tempoh'=>$s->kategori1===self::KIDNEY?(new DefinisiBacaanMeter($this->year))->duration($s->tarikh_mula,$s->tarikh_tamat):null];}
    public function preview(stdClass $s): ?string
    {
        if(!isset($this->references()[$s->kategori1??'']))return null;$total=BigDecimal::of('1');
        foreach(['bil_unit','kadar','kekerapan','bil_bulan'] as $key){if(($s->$key??null)===null||$s->$key==='')return null;$total=$total->multipliedBy($s->$key);}
        $value=(string)$total;return str_contains($value,'.')?rtrim(rtrim($value,'0'),'.'):$value;
    }
}
