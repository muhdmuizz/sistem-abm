<?php

namespace App\Services;

use App\Models\RujukanOs;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use stdClass;

/** Approved monthly budgeting source adapter; no card operations or official output. */
class DefinisiTouchNgo extends DefinisiAnggaran
{
    public const VERSION='os27000.touchngo.v1';

    public function supports(RujukanOs $os): bool
    {
        return $os->kod_objek==='27000' && !$os->dikecualikan && $os->butiran_perbelanjaan==='Bekalan dan Bahan-bahan Lain';
    }

    public function columns(): array
    {
        return [
            ['key'=>'tempat_bertugas_id','context'=>true,'label'=>'Tempat Bertugas','type'=>'select','options'=>'places','width'=>230],
            ['key'=>'tujuan','label'=>'Tujuan','type'=>'text','maxLength'=>500,'width'=>280],
            ['key'=>'bil_unit','label'=>'Bil. Unit','type'=>'decimal','width'=>120],
            ['key'=>'kadar','label'=>'Kadar Bulanan (RM)','type'=>'decimal','money'=>true,'width'=>180],
            ['key'=>'bil_bulan','label'=>'Bilangan Bulan','type'=>'decimal','width'=>140],
            ['key'=>'preview','label'=>'Anggaran Setahun (RM) — Pratonton','type'=>'output','money'=>true,'numeric'=>true,
                'formula'=>['operation'=>'multiply','inputs'=>['bil_unit','kadar','bil_bulan']],'width'=>200],
        ];
    }

    public function selector(): string { return ''; }
    public function references(): array { return []; }
    public function inputHelp(): string { return 'Isi tujuan, Bil. Unit, anggaran Kadar Bulanan dan bilangan bulan untuk Touch ’n Go.'; }

    public function validate(array $rows): array
    {
        foreach($rows as &$row){if(is_array($row)&&array_key_exists('kadar',$row))$row['kadar']=SumberWang::normalise($row['kadar']);}unset($row);
        $decimal=['nullable','string','max:128','regex:/^-?\d+(?:\.\d+)?$/D'];
        return Validator::make(['rekod'=>$rows],[
            'rekod'=>'present|array','rekod.*'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,tujuan,bil_unit,kadar,bil_bulan',
            'rekod.*.butiran_id'=>'sometimes|required|integer|min:1|distinct','rekod.*.semakan_butiran'=>'sometimes|required|integer|min:0',
            'rekod.*.tempat_bertugas_id'=>'required|integer|min:1','rekod.*.tujuan'=>'nullable|string|max:500',
            'rekod.*.bil_unit'=>$decimal,'rekod.*.kadar'=>$decimal,'rekod.*.bil_bulan'=>$decimal,
        ],['regex'=>'Gunakan nombor perpuluhan biasa yang sah.','array'=>'Struktur baris tidak sah.'])->validate()['rekod'];
    }

    public function source(array $row): stdClass
    {
        return (object)['definition'=>self::VERSION,'tujuan'=>$row['tujuan'] ?? null,'bil_unit'=>$row['bil_unit'] ?? null,
            'kadar'=>$row['kadar'] ?? null,'bil_bulan'=>$row['bil_bulan'] ?? null];
    }

    public function readable(mixed $source): bool
    {
        $keys=['definition','tujuan','bil_unit','kadar','bil_bulan'];
        if(!$source instanceof stdClass||($source->definition ?? null)!==self::VERSION||
            array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        if(SumberWang::normalise($source->kadar)!==$source->kadar)return false;
        $row=get_object_vars($source);unset($row['definition']);$row['tempat_bertugas_id']=1;
        try{$this->validate([$row]);}catch(\Illuminate\Validation\ValidationException){return false;}
        return true;
    }

    public function preview(stdClass $source): ?string
    {
        $factors=[$source->bil_unit ?? null,$source->kadar ?? null,$source->bil_bulan ?? null];
        if(in_array(null,$factors,true)||in_array('',$factors,true))return null;
        $value=BigDecimal::of('1');foreach($factors as $factor)$value=$value->multipliedBy($factor);
        $text=(string)$value;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
