<?php

namespace App\Services;

use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use stdClass;

/** Whole-activity draft envelopes; components remain independently priced inputs. */
class DefinisiUmumBerkumpulan extends DefinisiUmum
{
    public const GROUP_VERSION = 'os29000.umum.v2';
    private const SHARED = ['tujuan','tarikh_mula','tarikh_tamat'];
    private const COMPONENT = ['kategori1','kategori2','nama','jawatan','gred','bil_unit','kadar'];
    private const SINGLE_ID = '00000000-0000-4000-8000-000000000001';

    public function validate(array $rows): array
    {
        $result=[];
        foreach($rows as $i=>$row){
            if(is_array($row)&&!array_key_exists('komponen',$row)){
                try{$result[]=parent::validate([$row])[0];}
                catch(ValidationException $e){throw ValidationException::withMessages($this->errors($e,$i));}
                continue;
            }
            try{
                $list=function($attribute,$value,$fail){if(!is_array($value)||!array_is_list($value))$fail('Gunakan senarai komponen tersusun.');};
                $data=Validator::make(['rekod'=>[$row]],[
                    'rekod.0'=>'required|array:butiran_id,semakan_butiran,tempat_bertugas_id,tujuan,tarikh_mula,tarikh_tamat,komponen',
                    'rekod.0.komponen'=>['present','array',$list],
                    'rekod.0.komponen.*'=>'required|array:id,kategori1,bil_unit,kadar,bil_hari',
                    'rekod.0.komponen.*.id'=>'required|uuid|distinct',
                ])->validate()['rekod'][0];
                $shared=array_diff_key($data,['komponen'=>true]);
                // Validate shared ownership/date fields even in a component-free partial draft.
                parent::validate([$shared]);
                $components=[];
                foreach($data['komponen'] as $j=>$component){
                    $id=$component['id'];unset($component['id']);
                    try{$validated=parent::validate([$shared+$component])[0];}
                    catch(ValidationException $e){
                        $errors=[];
                        foreach($e->errors() as $key=>$messages){$field=substr($key,8);$errors['rekod.0.'.(in_array($field,self::COMPONENT,true)?'komponen.'.$j.'.':'').$field]=$messages;}
                        throw ValidationException::withMessages($errors);
                    }
                    $components[]=['id'=>$id]+array_intersect_key($validated,array_flip(self::COMPONENT));
                }
                $result[]=$shared+['komponen'=>$components];
            }catch(ValidationException $e){throw ValidationException::withMessages($this->errors($e,$i));}
        }
        $ids=array_column($result,'butiran_id');
        if(count($ids)!==count(array_unique($ids)))throw ValidationException::withMessages(['rekod'=>'Butiran berulang tidak dibenarkan.']);
        return $result;
    }
    private function errors(ValidationException $e,int $index): array
    {
        $errors=[];foreach($e->errors() as $key=>$messages)$errors[preg_replace('/^rekod\.0(?=\.|$)/','rekod.'.$index,$key)]=$messages;return $errors;
    }
    public function hasInput(array $row): bool
    {
        if(!array_key_exists('komponen',$row))return parent::hasInput($row);
        foreach(self::SHARED as $key)if(($row[$key]??null)!==null&&$row[$key]!=='')return true;
        foreach($row['komponen'] as $component)foreach(self::COMPONENT as $key)if(($component[$key]??null)!==null&&$component[$key]!=='')return true;
        return false;
    }
    public function source(array $row): stdClass
    {
        if(!array_key_exists('komponen',$row))return parent::source($row);
        $data=['definition'=>self::GROUP_VERSION];foreach(self::SHARED as $key)$data[$key]=$row[$key]??null;
        $data['komponen']=array_map(function($component){$data=['id'=>$component['id']];foreach(self::COMPONENT as $key)$data[$key]=$component[$key]??null;return (object)$data;},$row['komponen']);
        return (object)$data;
    }
    public function readable(mixed $source): bool
    {
        if(($source instanceof stdClass)&&($source->definition??null)===parent::VERSION)return parent::readable($source);
        $keys=['definition','tujuan','tarikh_mula','tarikh_tamat','komponen'];
        if(!$source instanceof stdClass||($source->definition??null)!==self::GROUP_VERSION||array_diff($keys,array_keys(get_object_vars($source)))||array_diff(array_keys(get_object_vars($source)),$keys))return false;
        if(!is_array($source->komponen)||!array_is_list($source->komponen))return false;
        try{
            $row=json_decode(json_encode($source,JSON_THROW_ON_ERROR),true,512,JSON_THROW_ON_ERROR);unset($row['definition']);$row['tempat_bertugas_id']=1;
            $this->validate([$row]);
            // Normalisation may accept grouped input, but saved source rates must be canonical.
            foreach($source->komponen as $component){
                $expected=['id',...self::COMPONENT];
                if(!$component instanceof stdClass||array_diff($expected,array_keys(get_object_vars($component)))||array_diff(array_keys(get_object_vars($component)),$expected)||SumberWang::normalise($component->kadar)!==$component->kadar)return false;
            }
            return true;
        }catch(ValidationException|\JsonException $e){return false;}
    }
    public function project(stdClass $source): array
    {
        $shared=[];foreach(self::SHARED as $key)$shared[$key]=$source->{$key}??null;
        if(($source->definition??null)===parent::VERSION){$component=['id'=>self::SINGLE_ID];foreach(self::COMPONENT as $key)$component[$key]=$source->{$key}??null;$components=[$component];}
        else $components=array_map(fn($c)=>get_object_vars($c),$source->komponen);
        return $shared+['komponen'=>$components,'tempoh'=>$this->duration($source)];
    }
    public function preview(stdClass $source): ?string
    {
        if(($source->definition??null)===parent::VERSION)return parent::preview($source);
        if(!$source->komponen)return null;$sum=BigDecimal::of('0');
        foreach($source->komponen as $component){$value=parent::preview($component);if($value===null)return null;$sum=$sum->plus($value);}
        $text=(string)$sum;return str_contains($text,'.')?rtrim(rtrim($text,'0'),'.'):$text;
    }
}
