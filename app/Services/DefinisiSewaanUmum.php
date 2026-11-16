<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Approved 64-path rental template; separate from photocopier rental. */
class DefinisiSewaanUmum extends DefinisiAnggaran
{
    public const VERSION='os24000.sewaan-umum.v1';
    private const FIELDS=['tujuan','kategori1','kategori2','kategori3','tarikh_mula','tarikh_tamat','bil_unit','kadar','bil_bulan'];
    public function __construct(private int $year) {}
    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek==='24000' && $os->butiran_perbelanjaan==='Sewaan' && !$os->dikecualikan;
    }
    public function childSelector(): string {return 'kategori2';}
    public function inputHelp(): string
    {
        return 'Pilih kategori sewaan, peralatan dan jenisnya. Tanah tidak mempunyai Jenis Peralatan. Tarikh dan Tempoh hanya untuk Berkontrak; Bilangan Bulan diisi sendiri bagi kedua-dua kategori.';
    }
    public function references(): array
    {
        $handle=fopen(base_path('docs/references/OS24000-sewaan-umum-kategori.csv'),'rb');
        if($handle===false)throw new RuntimeException('Rujukan Sewaan tidak tersedia.');
        try {
            if(fgetcsv($handle,0,',','"','')!==['Kategori1','Kategori2','Kategori3','Kadar Mode'])throw new RuntimeException('Struktur rujukan Sewaan tidak sah.');
            $rows=[];$count=0;
            while(($row=fgetcsv($handle,0,',','"',''))!==false){
                if(count($row)!==4||!in_array($row[0],['Berkontrak','Tidak Berkontrak'],true)||$row[1]===''||($row[2]===''&&$row[1]!=='Tanah')||$row[3]!=='USER_INPUT_KADAR'||isset($rows[$row[0]][$row[1]][$row[2]]))throw new RuntimeException('Rekod rujukan Sewaan tidak sah.');
                $rows[$row[0]][$row[1]][$row[2]]=['mode'=>$row[3]];$count++;
            }
            if($count!==64||array_keys($rows)!==['Berkontrak','Tidak Berkontrak'])throw new RuntimeException('Rujukan Sewaan tidak lengkap.');
            foreach($rows as $branches){$size=0;foreach($branches as $types)$size+=count($types);if($size!==32||!isset($branches['Tanah']['']))throw new RuntimeException('Laluan Sewaan tidak lengkap.');}
            return $rows;
        }finally{fclose($handle);}
    }
    public function columns(): array
    {
        $contract=['field'=>'kategori1','value'=>'Berkontrak'];
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Nama Program/ Aktiviti/ Tujuan','type'=>'text','maxLength'=>1000,'width'=>300],
            ['key'=>'kategori1','label'=>'Kategori1 — Kategori Sewaan','type'=>'select','options'=>'references','width'=>190],
            ['key'=>'kategori2','label'=>'Kategori2 — Kategori Peralatan','type'=>'select','dependsOn'=>'kategori1','width'=>310],
            ['key'=>'kategori3','label'=>'Kategori3 — Jenis Peralatan','type'=>'select','dependsOn'=>'kategori2','referenceParents'=>['kategori1','kategori2'],'referenceLeaf'=>true,'inactiveWhen'=>['field'=>'kategori2','value'=>'Tanah'],'width'=>390],
            ['key'=>'tarikh_mula','label'=>'Tarikh Mula','type'=>'date','min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'activeWhen'=>$contract,'width'=>160],
            ['key'=>'tarikh_tamat','label'=>'Tarikh Tamat','type'=>'date','min'=>sprintf('%04d-01-01',$this->year),'max'=>sprintf('%04d-12-31',$this->year),'activeWhen'=>$contract,'width'=>160],
            ['key'=>'tempoh','label'=>'Tempoh (Hari)','type'=>'output','activeWhen'=>$contract,'numeric'=>true,'formula'=>['operation'=>'inclusiveDays','inputs'=>['tarikh_mula','tarikh_tamat']],'width'=>130],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>120],
            ['key'=>'kadar','label'=>'Kadar (RM)','type'=>'decimal','money'=>true,'width'=>160],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>140],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kadar','bil_bulan'],'requiresReference'=>true],'width'=>210],
        ];
    }
    public function validate(array $rows): array
    {
        $refs=$this->references();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $validator=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1','rekod.*.tujuan'=>'nullable|string|max:1000',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($refs))],
            'rekod.*.kategori2'=>'nullable|string|max:1000','rekod.*.kategori3'=>'nullable|string|max:1000',
            'rekod.*.tarikh_mula'=>'bail|nullable|string|date_format:Y-m-d','rekod.*.tarikh_tamat'=>'bail|nullable|string|date_format:Y-m-d',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kadar'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ],['in'=>'Pilih Kategori Sewaan yang sah.','date_format'=>'Gunakan tarikh yang sah.','regex'=>'Gunakan nombor perpuluhan biasa tanpa pemisah ribu.','array'=>'Struktur baris tidak sah.']);
        $validator->after(function($validator) use($rows,$refs){
            foreach($rows as $i=>$row){
                if(!is_array($row))continue;$prefix='rekod.'.$i.'.';
                $one=$row['kategori1'] ?? null;$two=$row['kategori2'] ?? null;$three=$row['kategori3'] ?? null;
                // Type-invalid inputs are already errors; never use them as array offsets.
                if(is_string($two)&&$two!==''&&(!is_string($one)||!isset($refs[$one][$two])))$validator->errors()->add($prefix.'kategori2','Pilih peralatan di bawah Kategori Sewaan yang sah.');
                if(is_string($three)&&$three!==''&&(!is_string($one)||!is_string($two)||!isset($refs[$one][$two][$three])))$validator->errors()->add($prefix.'kategori3','Pilih Jenis Peralatan yang sah; Tanah tidak mempunyai kategori ketiga.');
                foreach(['tarikh_mula','tarikh_tamat'] as $field){
                    $value=$row[$field] ?? null;if($value===null||$value==='')continue;
                    if($one!=='Berkontrak')$validator->errors()->add($prefix.$field,'Tarikh hanya terpakai untuk Berkontrak.');
                    elseif(is_string($value)&&!$validator->errors()->has($prefix.$field)&&substr($value,0,4)!==sprintf('%04d',$this->year))$validator->errors()->add($prefix.$field,'Tarikh mesti dalam tahun Kitaran.');
                }
                if(is_string($row['tarikh_mula'] ?? null)&&is_string($row['tarikh_tamat'] ?? null)&&$row['tarikh_mula']!==''&&$row['tarikh_tamat']!==''&&!$validator->errors()->has($prefix.'tarikh_mula')&&!$validator->errors()->has($prefix.'tarikh_tamat')&&$row['tarikh_tamat']<$row['tarikh_mula'])$validator->errors()->add($prefix.'tarikh_tamat','Tarikh Tamat tidak boleh mendahului Tarikh Mula.');
            }
        });
        return $validator->validate()['rekod'];
    }
    public function source(array $row): stdClass
    {
        $data=['definition'=>self::VERSION];foreach(self::FIELDS as $key)$data[$key]=$row[$key] ?? null;
        if(($data['kategori3'] ?? null)==='')$data['kategori3']=null;
        return (object)$data;
    }
    public function readable(mixed $source): bool
    {
        $keys=['definition',...self::FIELDS];
        if(!$source instanceof stdClass||($source->definition ?? null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;
        try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}return true;
    }
    public function duration(stdClass $source): ?int
    {
        if(($source->kategori1 ?? null)!=='Berkontrak')return null;
        $start=$source->tarikh_mula ?? null;$end=$source->tarikh_tamat ?? null;
        if(!is_string($start)||!is_string($end)||$start===''||$end==='')return null;
        $a=DateTimeImmutable::createFromFormat('!Y-m-d',$start);$b=DateTimeImmutable::createFromFormat('!Y-m-d',$end);
        if(!$a||!$b||$a->format('Y-m-d')!==$start||$b->format('Y-m-d')!==$end||$b<$a)return null;
        return $a->diff($b)->days+1;
    }
    public function project(stdClass $source): array {return parent::project($source)+['tempoh'=>$this->duration($source)];}
    public function preview(stdClass $source): ?string
    {
        if(!isset($this->references()[$source->kategori1 ?? ''][$source->kategori2 ?? ''][$source->kategori3 ?? '']))return null;
        $factors=[$source->bil_unit ?? null,$source->kadar ?? null,$source->bil_bulan ?? null];
        if(in_array(null,$factors,true)||in_array('',$factors,true))return null;
        $value=BigDecimal::of('1');foreach($factors as $factor)$value=$value->multipliedBy($factor);
        $text=(string)$value;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
