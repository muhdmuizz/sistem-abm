<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;
use stdClass;

class DefinisiPenyelenggaraan extends DefinisiAnggaran
{
    public const VERSION = 'os28000.v1';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek === '28000' && ! $os->dikecualikan &&
            $os->butiran_perbelanjaan === 'Penyelenggaraan dan Pembaikan Kecil Yang Dibeli';
    }

    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>220],
            ['key'=>'tujuan','label'=>'Tujuan/ Perihal Aset','type'=>'text','maxLength'=>1000,'width'=>230],
            ['key'=>'kategori1','label'=>'Kategori1 — Kategori','type'=>'select','options'=>'references','width'=>280],
            ['key'=>'kategori2','label'=>'Kategori2 — Peralatan','type'=>'select','dependsOn'=>'kategori1','width'=>210],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>120],
            ['key'=>'kadar','label'=>'KADAR (RM)','type'=>'decimal','money'=>true,'mixed'=>true,'width'=>180],
            ['key'=>'kekerapan','label'=>'Kekerapan Sebulan','type'=>'decimal','width'=>150],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>140],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','numeric'=>true,'money'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kadar','kekerapan','bil_bulan']],'width'=>200],
        ];
    }

    public function selector(): string { return 'kategori1'; }
    public function childSelector(): string { return 'kategori2'; }
    public function inputHelp(): string { return 'Kekerapan ialah bilangan penyelenggaraan sebulan. KADAR rujukan diisi automatik; kadar lain diisi sendiri.'; }

    public function references(): array
    {
        $handle=fopen(base_path('docs/references/OS28000-penyelenggaraan-kategori-kadar.csv'),'rb');
        if ($handle===false) { throw new RuntimeException('Rujukan penyelenggaraan tidak tersedia.'); }
        try {
            if (fgetcsv($handle,0,',','"','')!==['Kategori1','Kategori2','Kadar (RM; Reference)','Kadar Mode']) { throw new RuntimeException('Struktur rujukan penyelenggaraan tidak sah.'); }
            $result=[];$count=0;$fixed=0;
            while (($row=fgetcsv($handle,0,',','"',''))!==false) {
                if(count($row)!==4 || isset($result[$row[0]][$row[1]]) ||
                    !in_array($row[3],['USER_INPUT_KADAR','FIXED_REFERENCE'],true) ||
                    ($row[3]==='USER_INPUT_KADAR' ? $row[2]!=='' : !preg_match('/^\d+\.\d{2}$/D',$row[2]))) {
                    throw new RuntimeException('Rekod penyelenggaraan tidak sah.');
                }
                $count++;$fixed+=(int)($row[3]==='FIXED_REFERENCE');
                $result[$row[0]][$row[1]]=['mode'=>$row[3],'kadar'=>$row[2]===''?null:$row[2]];
            }
            if($count!==10 || count($result)!==4 || $fixed!==2 ||
                ($result['Tebang Pokok']['']['kadar'] ?? null)!=='1500.00' ||
                ($result['Penyelenggaraan Alat Pemadam Api']['Alat Pemadam Api']['kadar'] ?? null)!=='100.00') {
                throw new RuntimeException('Rujukan penyelenggaraan tidak lengkap.');
            }
            return $result;
        } finally { fclose($handle); }
    }

    public function validate(array $rows): array
    {
        foreach($rows as &$row) { if(is_array($row)&&array_key_exists('kadar',$row))$row['kadar']=SumberWang::normalise($row['kadar']); } unset($row);
        $refs=$this->references();$decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        $validator=Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array',
            'rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,tujuan,kategori1,kategori2,bil_unit,kadar,kekerapan,bil_bulan',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1','rekod.*.tujuan'=>'nullable|string|max:1000',
            'rekod.*.kategori1'=>['nullable','string',Rule::in(array_keys($refs))],
            'rekod.*.kategori2'=>'nullable|string|max:200',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kadar'=>$decimal,'rekod.*.kekerapan'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ],['regex'=>'Gunakan nombor perpuluhan yang sah.','in'=>'Pilih kategori yang sah.','array'=>'Struktur baris tidak sah.']);
        $validator->after(function($validator) use($rows,$refs) {
            foreach($rows as $i=>$row) {
                if(!is_array($row))continue;
                $parent=$row['kategori1'] ?? null;$child=$row['kategori2'] ?? null;
                if(!is_string($parent)||!isset($refs[$parent])) {
                    if($child!==null && $child!=='')$validator->errors()->add("rekod.$i.kategori2",'Pilih kategori dahulu.');
                    continue;
                }
                if($child!==null && !is_string($child))continue;
                // Blank is a real reference key only for Tebang Pokok. Other blanks remain partial drafts.
                if(($child!==null && $child!=='') || $parent==='Tebang Pokok') {
                    if(!array_key_exists($child ?? '',$refs[$parent])) { $validator->errors()->add("rekod.$i.kategori2",'Peralatan tidak sepadan dengan kategori.');continue; }
                    if($refs[$parent][$child ?? '']['mode']==='FIXED_REFERENCE' && ($row['kadar'] ?? null)!==null && $row['kadar']!=='') {
                        $validator->errors()->add("rekod.$i.kadar",'KADAR rujukan tidak boleh dipinda.');
                    }
                }
            }
        });
        return $validator->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        $source=['definition'=>self::VERSION];
        foreach(['tujuan','kategori1','kategori2','bil_unit','kadar','kekerapan','bil_bulan'] as $key)$source[$key]=$row[$key] ?? null;
        return (object)$source;
    }

    public function readable(mixed $source): bool
    {
        $keys=['definition','tujuan','kategori1','kategori2','bil_unit','kadar','kekerapan','bil_bulan'];
        if(!$source instanceof stdClass || ($source->definition ?? null)!==self::VERSION ||
            array_diff($keys,array_keys(get_object_vars($source))) || array_diff(array_keys(get_object_vars($source)),$keys))return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;
        try { $this->validate([$row]); } catch(\Illuminate\Validation\ValidationException) { return false; }
        return true;
    }

    public function preview(stdClass $source): ?string
    {
        $parent=$source->kategori1 ?? null;$child=$source->kategori2 ?? '';
        $ref=$this->references()[$parent ?? ''][$child ?? ''] ?? null;
        $rate=$ref && $ref['mode']==='FIXED_REFERENCE' ? $ref['kadar'] : ($source->kadar ?? null);
        $factors=[$source->bil_unit ?? null,$rate,$source->kekerapan ?? null,$source->bil_bulan ?? null];
        if(!$ref || in_array(null,$factors,true) || in_array('',$factors,true))return null;
        $value=BigDecimal::of('1');foreach($factors as $factor)$value=$value->multipliedBy($factor);
        $text=(string)$value;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
