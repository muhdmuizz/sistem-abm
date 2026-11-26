<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use DateTimeImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

/** Approved monthly-copy source template; distinct from OS24000 rental. */
class DefinisiBacaanMeter extends DefinisiAnggaran
{
    public const VERSION = 'os29000.bacaan-meter.v1';
    private const FIELDS = ['kategori1','nama','tarikh_mula','tarikh_tamat','tarikh_mula_kontrak','tarikh_tamat_kontrak','bil_unit','lokasi','kadar','bil_bulan'];

    public function __construct(private int $year) {}

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek === '29000' && $os->butiran_perbelanjaan === 'Bacaan Meter Mesin Fotostat' && !$os->dikecualikan;
    }

    public function references(): array
    {
        $handle = fopen(base_path('docs/references/OS24000-sewaan-mesin-fotostat-kategori.csv'), 'rb');
        if ($handle === false) throw new RuntimeException('Rujukan Jenis Cetakan tidak tersedia.');
        try {
            if (fgetcsv($handle,0,',','"','') !== ['Kategori1','Kadar Mode']) throw new RuntimeException('Struktur Jenis Cetakan tidak sah.');
            $rows=[];
            while (($row=fgetcsv($handle,0,',','"','')) !== false) {
                if (count($row)!==2 || isset($rows[$row[0]]) || $row[1]!=='USER_INPUT_KADAR') throw new RuntimeException('Rekod Jenis Cetakan tidak sah.');
                $rows[$row[0]]=['mode'=>$row[1]];
            }
            if (array_keys($rows)!==['Berwarna','Hitam dan Putih']) throw new RuntimeException('Rujukan Jenis Cetakan tidak lengkap.');
            return $rows;
        } finally { fclose($handle); }
    }

    public function inputHelp(): string
    {
        return 'Bil. Salinan ialah anggaran salinan sebulan. Kadar ialah RM sesalinan. Bilangan Bulan diisi sendiri; kedua-dua tempoh kontrak tidak menetapkannya. Kontrak Keseluruhan boleh merentasi tahun.';
    }

    public function columns(): array
    {
        $min=sprintf('%04d-01-01',$this->year); $max=sprintf('%04d-12-31',$this->year);
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Jenis Cetakan','type'=>'select','options'=>'references','width'=>220],
            ['key'=>'nama','label'=>'Nama Syarikat','type'=>'text','maxLength'=>1000,'width'=>280],
            ['key'=>'tarikh_mula','label'=>'Tarikh Mula Kontrak Semasa','type'=>'date','min'=>$min,'max'=>$max,'width'=>190],
            ['key'=>'tarikh_tamat','label'=>'Tarikh Tamat Kontrak Semasa','type'=>'date','min'=>$min,'max'=>$max,'width'=>190],
            ['key'=>'tempoh','label'=>'Tempoh Kontrak Semasa','type'=>'output','formula'=>['operation'=>'inclusiveCalendar','inputs'=>['tarikh_mula','tarikh_tamat']],'width'=>200],
            ['key'=>'tarikh_mula_kontrak','label'=>'Tarikh Mula Kontrak Keseluruhan','type'=>'date','min'=>'0001-01-01','max'=>'9999-12-31','width'=>210],
            ['key'=>'tarikh_tamat_kontrak','label'=>'Tarikh Tamat Kontrak Keseluruhan','type'=>'date','min'=>'0001-01-01','max'=>'9999-12-31','width'=>210],
            ['key'=>'tempoh_kontrak','label'=>'Tempoh Kontrak Keseluruhan','type'=>'output','formula'=>['operation'=>'inclusiveCalendar','inputs'=>['tarikh_mula_kontrak','tarikh_tamat_kontrak']],'width'=>200],
            ['key'=>'bil_unit','label'=>'Bil. Salinan (Sebulan)','type'=>'decimal','width'=>160],
            ['key'=>'lokasi','label'=>'Lokasi Mesin Fotostat','type'=>'text','maxLength'=>1000,'width'=>250],
            ['key'=>'kadar','label'=>'Kadar (RM/ Salinan)','type'=>'decimal','money'=>true,'width'=>160],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>140],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kadar','bil_bulan'],'requiresReference'=>true],'width'=>210],
        ];
    }

    public function validate(array $rows): array
    {
        $decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $rules=[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,'.implode(',',self::FIELDS),
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($this->references()))],
            'rekod.*.lokasi'=>'nullable|string|max:1000',
            'rekod.*.nama'=>'nullable|string|max:1000',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kadar'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ];
        foreach (['tarikh_mula','tarikh_tamat','tarikh_mula_kontrak','tarikh_tamat_kontrak'] as $field) $rules['rekod.*.'.$field]='bail|nullable|string|date_format:Y-m-d';
        $validator=Validator::make(['rekod'=>$rows],$rules,[
            'in'=>'Pilih Jenis Cetakan yang sah.','date_format'=>'Gunakan tarikh yang sah.',
            'regex'=>'Gunakan nombor perpuluhan biasa tanpa pemisah ribu.','array'=>'Struktur baris tidak sah.',
        ]);
        $validator->after(function($validator) use($rows) {
            foreach($rows as $i=>$row) {
                if(!is_array($row))continue;
                foreach(['tarikh_mula','tarikh_tamat','tarikh_mula_kontrak','tarikh_tamat_kontrak'] as $field) {
                    $key='rekod.'.$i.'.'.$field; $value=$row[$field] ?? null;
                    if(!is_string($value)||$value===''||$validator->errors()->has($key))continue;
                    if(substr($value,0,4)==='0000')$validator->errors()->add($key,'Gunakan tahun yang sah.');
                    elseif(in_array($field,['tarikh_mula','tarikh_tamat'],true)&&substr($value,0,4)!==sprintf('%04d',$this->year))$validator->errors()->add($key,'Kontrak Semasa mesti dalam tahun Kitaran.');
                }
                foreach([['tarikh_mula','tarikh_tamat'],['tarikh_mula_kontrak','tarikh_tamat_kontrak']] as [$start,$end]) {
                    if(!empty($row[$start])&&!empty($row[$end])&&!$validator->errors()->has('rekod.'.$i.'.'.$start)&&!$validator->errors()->has('rekod.'.$i.'.'.$end)&&$row[$end]<$row[$start])$validator->errors()->add('rekod.'.$i.'.'.$end,'Tarikh Tamat tidak boleh mendahului Tarikh Mula.');
                }
            }
        });
        return $validator->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        $data=['definition'=>self::VERSION];
        foreach(self::FIELDS as $key)$data[$key]=$row[$key] ?? null;
        return (object)$data;
    }

    public function readable(mixed $source): bool
    {
        $keys=['definition',...self::FIELDS];
        if(!$source instanceof stdClass||($source->definition ?? null)!==self::VERSION||array_diff($keys,array_keys(get_object_vars($source)),['nama'])||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;
        try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}
        return true;
    }

    /** Inclusive interval: clamped complete calendar years, then months, then remaining days. */
    public function duration(?string $start, ?string $end): ?string
    {
        if(!$start||!$end)return null;
        $a=DateTimeImmutable::createFromFormat('!Y-m-d',$start);$b=DateTimeImmutable::createFromFormat('!Y-m-d',$end);
        if(!$a||!$b||$a->format('Y-m-d')!==$start||$b->format('Y-m-d')!==$end||$b<$a)return null;
        $limit=$b->modify('+1 day');
        $shift=static function(DateTimeImmutable $date,int $months): DateTimeImmutable {
            $first=$date->modify('first day of this month')->modify('+'.$months.' months');
            return $first->setDate((int)$first->format('Y'),(int)$first->format('n'),min((int)$date->format('j'),(int)$first->format('t')));
        };
        $years=(int)$limit->format('Y')-(int)$a->format('Y');
        if($shift($a,$years*12)>$limit)$years--;
        $anchor=$shift($a,$years*12);$months=((int)$limit->format('Y')-(int)$anchor->format('Y'))*12+(int)$limit->format('n')-(int)$anchor->format('n');
        if($shift($anchor,$months)>$limit)$months--;
        $days=$shift($anchor,$months)->diff($limit)->days;
        return $years.' tahun, '.$months.' bulan, '.$days.' hari';
    }

    public function project(stdClass $source): array
    {
        return parent::project($source)+['tempoh'=>$this->duration($source->tarikh_mula,$source->tarikh_tamat),'tempoh_kontrak'=>$this->duration($source->tarikh_mula_kontrak,$source->tarikh_tamat_kontrak)];
    }

    public function preview(stdClass $source): ?string
    {
        if(!isset($this->references()[$source->kategori1 ?? '']))return null;
        $factors=[$source->bil_unit ?? null,$source->kadar ?? null,$source->bil_bulan ?? null];
        if(in_array(null,$factors,true)||in_array('',$factors,true))return null;
        $value=BigDecimal::of('1');foreach($factors as $factor)$value=$value->multipliedBy($factor);
        $text=(string)$value;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
