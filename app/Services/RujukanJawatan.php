<?php

namespace App\Services;

use RuntimeException;

/** Shared personnel names projected from Reference 07; no duplicated OS-specific roster. */
class RujukanJawatan
{
    public function names(): array
    {
        $handle=fopen(base_path('docs/references/jawatan-gred-gaji-kgt.csv'),'rb');
        if($handle===false)throw new RuntimeException('Rujukan Jawatan tidak tersedia.');
        try {
            if(fgetcsv($handle,0,',','"','')!==['KLASIFIKASI PERKHIDMATAN','SKIM PERKHIDMATAN/ JAWATAN','SSPA','GAJI MINIMUM','GAJI MAKSIMUM','GAJI MEDIAN','KGT'])throw new RuntimeException('Struktur rujukan Jawatan tidak sah.');
            $names=[];$count=0;
            while(($row=fgetcsv($handle,0,',','"',''))!==false){
                if(count($row)!==7||$row[1]==='')throw new RuntimeException('Rekod Jawatan tidak sah.');
                $names[$row[1]]=true;$count++;
            }
            if($count!==131)throw new RuntimeException('Rujukan Jawatan tidak lengkap.');
            $values=array_keys($names);sort($values,SORT_STRING);return $values;
        } finally { fclose($handle); }
    }
}
