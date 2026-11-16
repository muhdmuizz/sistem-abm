<?php
namespace App\Services;
use App\Models\RujukanOs;
use stdClass;
class DefinisiSewaanFotostat extends DefinisiBacaanMeter {
 public const VERSION='os24000.fotostat.v1';
 public function supports(RujukanOs $os): bool {return $os->kod_objek==='24000'&&$os->butiran_perbelanjaan==='Sewaan Mesin Fotostat dan Riso'&&!$os->dikecualikan;}
 public function columns(): array {return array_map(function($c){if($c['key']==='bil_unit')$c['label']='Bil. Unit';if($c['key']==='kadar')$c['label']='Kadar (RM)';return $c;},parent::columns());}
 public function inputHelp(): string {return 'Bil. Unit, Kadar dan Bilangan Bulan ialah input sewaan. Tempoh kontrak tidak menetapkan bulan. Butiran Bacaan Meter dipautkan secara automatik; salinan, kadar sesalinan dan bulan meter diisi berasingan.';}
 public function source(array $row): stdClass {$s=parent::source($row);$s->definition=self::VERSION;return $s;}
 public function readable(mixed $s): bool {if(!$s instanceof stdClass||($s->definition??null)!==self::VERSION)return false;$copy=clone $s;$copy->definition=parent::VERSION;return parent::readable($copy);}
}
