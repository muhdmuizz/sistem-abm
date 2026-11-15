<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** One approved monthly MySTEP source template across existing catalogue variants. */
class DefinisiMystep extends DefinisiAnggaran
{
    public const VERSION='os29000.mystep.v1';
    public function __construct(private int $year) {}
    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek==='29000' && !$os->dikecualikan && in_array($os->butiran_perbelanjaan,
            ['MySTEP','MySTEP - Guru Ganti','MySTEP - Pembantu Pengurusan Murid (PPM) Ganti'],true);
    }
    private function choices(string $file,string $label): array
    {
        $handle=fopen(base_path('docs/references/'.$file),'rb');if($handle===false)throw new RuntimeException('Rujukan MySTEP tidak tersedia.');
        try{
            if(fgetcsv($handle,0,',','"','')!==['Source Row',$label])throw new RuntimeException('Struktur rujukan MySTEP tidak sah.');
            $values=[];$count=0;
            while(($row=fgetcsv($handle,0,',','"',''))!==false){$count++;if(count($row)!==2||$row[0]!==strval($count)||$row[1]==='')throw new RuntimeException('Rekod MySTEP tidak sah.');$values[]=$row[1];}
            if($count!==11)throw new RuntimeException('Rujukan MySTEP tidak lengkap.');return array_values(array_unique($values));
        }finally{fclose($handle);}
    }
    public function categories(): array {return $this->choices('OS29000-mystep-kategori1-source.csv','Kategori1');}
    public function purposes(): array {return $this->choices('OS29000-mystep-tujuan-source.csv','Tujuan Lantikan');}
    public function selector(): string {return 'kategori2';}
    public function inputHelp(): string {return 'Semua tujuan lantikan tersedia bagi setiap kategori. Kadar bulanan menggunakan JUMLAH rujukan Taraf Akademik. Isi bilangan orang dan bulan; tempoh tarikh tidak menetapkan bilangan bulan.';}
    public function columns(): array
    {
        $min=sprintf('%04d-01-01',$this->year);$max=sprintf('%04d-12-31',$this->year);
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — MySTEP','type'=>'select','options'=>'values','values'=>$this->categories(),'width'=>230],
            ['key'=>'tujuan','label'=>'Tujuan Lantikan','type'=>'select','options'=>'values','values'=>$this->purposes(),'width'=>290],
            ['key'=>'kategori2','label'=>'Kategori2 — Taraf Akademik','type'=>'select','options'=>'references','width'=>340],
            ['key'=>'tarikh_mula','label'=>'Tarikh Mula','type'=>'date','min'=>$min,'max'=>$max,'width'=>160],
            ['key'=>'tarikh_tamat','label'=>'Tarikh Tamat','type'=>'date','min'=>$min,'max'=>$max,'width'=>160],
            ['key'=>'tempoh','label'=>'Tempoh (Hari)','type'=>'output','numeric'=>true,'formula'=>['operation'=>'inclusiveDays','inputs'=>['tarikh_mula','tarikh_tamat']],'width'=>130],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>120],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>140],
            ['key'=>'unit','label'=>'Unit','type'=>'output','value'=>'Orang','width'=>90],
            ['key'=>'kadar','label'=>'Kadar Bulanan (RM/ Orang)','type'=>'output','reference'=>'kadar','money'=>true,'numeric'=>true,'width'=>180],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kadar','bil_bulan']],'width'=>210],
        ];
    }
    public function references(): array
    {
        $handle=fopen(base_path('docs/references/OS29000-mystep-taraf-akademik-kadar-source.csv'),'rb');if($handle===false)throw new RuntimeException('Kadar MySTEP tidak tersedia.');
        try{
            if(fgetcsv($handle,0,',','"','')!==['Taraf Akademik','Upah Tetap (RM)','KWSP (RM)','PERKESO (RM)','JUMLAH / Kadar (RM)'])throw new RuntimeException('Struktur kadar MySTEP tidak sah.');
            $rows=[];while(($row=fgetcsv($handle,0,',','"',''))!==false){if(count($row)!==5||$row[0]===''||!preg_match('/^\d+\.\d{2}$/D',$row[4])||isset($rows[$row[0]]))throw new RuntimeException('Rekod kadar MySTEP tidak sah.');$rows[$row[0]]=['kadar'=>$row[4]];}
            if(count($rows)!==4)throw new RuntimeException('Kadar MySTEP tidak lengkap.');return $rows;
        }finally{fclose($handle);}
    }
    public function validate(array $rows): array
    {
        $decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $validator=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,kategori1,tujuan,kategori2,tarikh_mula,tarikh_tamat,bil_unit,bil_bulan',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1','rekod.*.kategori1'=>['nullable','string',Rule::in($this->categories())],
            'rekod.*.tujuan'=>['nullable','string',Rule::in($this->purposes())],'rekod.*.kategori2'=>['nullable','string',Rule::in(array_keys($this->references()))],
            'rekod.*.tarikh_mula'=>'bail|nullable|string|date_format:Y-m-d','rekod.*.tarikh_tamat'=>'bail|nullable|string|date_format:Y-m-d',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ],['in'=>'Pilih nilai rujukan yang sah.','date_format'=>'Gunakan tarikh yang sah.','regex'=>'Gunakan nombor perpuluhan biasa.','array'=>'Struktur baris tidak sah.']);
        $validator->after(function($validator) use($rows){
            foreach($rows as $i=>$row){if(!is_array($row))continue;
                foreach(['tarikh_mula','tarikh_tamat'] as $field){$key='rekod.'.$i.'.'.$field;$value=$row[$field] ?? null;if(is_string($value)&&$value!==''&&!$validator->errors()->has($key)&&substr($value,0,4)!==sprintf('%04d',$this->year))$validator->errors()->add($key,'Tarikh mesti dalam tahun Kitaran.');}
                if(!empty($row['tarikh_mula'])&&!empty($row['tarikh_tamat'])&&!$validator->errors()->has('rekod.'.$i.'.tarikh_mula')&&!$validator->errors()->has('rekod.'.$i.'.tarikh_tamat')&&$row['tarikh_tamat']<$row['tarikh_mula'])$validator->errors()->add('rekod.'.$i.'.tarikh_tamat','Tarikh Tamat tidak boleh mendahului Tarikh Mula.');
            }
        });return $validator->validate()['rekod'];
    }
    public function source(array $row): stdClass
    {
        $data=['definition'=>self::VERSION];foreach(['kategori1','tujuan','kategori2','tarikh_mula','tarikh_tamat','bil_unit','bil_bulan'] as $key)$data[$key]=$row[$key] ?? null;return (object)$data;
    }
    public function readable(mixed $source): bool
    {
        $keys=['definition','kategori1','tujuan','kategori2','tarikh_mula','tarikh_tamat','bil_unit','bil_bulan'];
        if(!$source instanceof stdClass||($source->definition ?? null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;
        try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function duration(stdClass $source): ?int
    {
        $start=$source->tarikh_mula ?? null;$end=$source->tarikh_tamat ?? null;
        if(!is_string($start)||!is_string($end)||$start===''||$end==='')return null;
        $a=DateTimeImmutable::createFromFormat('!Y-m-d',$start);$b=DateTimeImmutable::createFromFormat('!Y-m-d',$end);
        if(!$a||!$b||$a->format('Y-m-d')!==$start||$b->format('Y-m-d')!==$end||$b<$a)return null;
        return $a->diff($b)->days+1;
    }
    public function project(stdClass $source): array {return parent::project($source)+['tempoh'=>$this->duration($source)];}
    public function preview(stdClass $source): ?string
    {
        $rate=$this->references()[$source->kategori2 ?? '']['kadar'] ?? null;$factors=[$source->bil_unit ?? null,$rate,$source->bil_bulan ?? null];
        if(in_array(null,$factors,true)||in_array('',$factors,true))return null;$value=BigDecimal::of('1');foreach($factors as $factor)$value=$value->multipliedBy($factor);
        $text=(string)$value;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
