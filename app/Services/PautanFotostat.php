<?php
namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\{Anggaran,Bidaan,ButiranAnggaran,RujukanOs};
use Illuminate\Support\Facades\DB;
use stdClass;

/** All linked writes remain under the existing actor/cycle/Bidaan transaction locks. */
class PautanFotostat
{
 public const SHARED=['kategori1','lokasi','tarikh_mula','tarikh_tamat','tarikh_mula_kontrak','tarikh_tamat_kontrak'];
 public const FINANCIAL=['bil_unit','kadar','bil_bulan'];
 private bool $internal=false;
 public function __construct(private KawalanPenyediaanKewangan $guard,private JejakAudit $audit){}
 public function rental(RujukanOs $os): bool {return $os->kod_objek==='24000'&&$os->butiran_perbelanjaan==='Sewaan Mesin Fotostat dan Riso';}
 public function meter(RujukanOs $os): bool {return $os->kod_objek==='29000'&&$os->butiran_perbelanjaan==='Bacaan Meter Mesin Fotostat';}
 public function pair(Bidaan $bid): array {
  $code=match($bid->aktivitiKewangan->nama){'Pengurusan Pentadbiran Negeri'=>'010807','Pendidikan Rendah'=>'030221','Pendidikan Menengah'=>'030303','Pengurusan Asrama'=>'030315',default=>null};
  abort_unless($code,404);
  $find=function($object,$label)use($bid,$code){return RujukanOs::untukPelaksanaan()->where('kod_objek',$object)->where('butiran_perbelanjaan',$label)->whereHas('pemetaan',fn($q)=>$q->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)->whereHas('kodAktiviti',fn($k)=>$k->where('kod_aktiviti',$code)->where('aktiviti_kewangan_id',$bid->aktiviti_kewangan_id)))->firstOrFail();};
  return [$find('24000','Sewaan Mesin Fotostat dan Riso'),$find('29000','Bacaan Meter Mesin Fotostat')];
 }
 public function active(Bidaan $bid): array {
  [$r,$m]=$this->pair($bid);
  $rental=$bid->anggaran()->where('rujukan_os_id',$r->id)->first();$meter=$bid->anggaran()->where('rujukan_os_id',$m->id)->first();
  abort_unless($rental?->diaktifkan&&$meter?->diaktifkan,409,'Aktifkan pasangan melalui OS24000 Sewaan Mesin Fotostat dan Riso.');
  return [$rental,$meter];
 }
 public function authority(Bidaan $bid,?string $reason,?int $place=null): void {
  $actor=$this->guard->actor();foreach($this->pair($bid) as $os)$this->guard->authorise($actor,Peranan::PenyediaAnggaran,$bid->kitaran_id,$bid->ptj_id,$bid->aktiviti_kewangan_id,$os->id,$reason,$place);
 }
 public function beforeSave(Bidaan $bid,Anggaran $estimate,?ButiranAnggaran $source,stdClass $data,int $place,?string $reason): void {
  $os=$estimate->rujukanOs;if(!$this->rental($os)&&!$this->meter($os))return;
  $this->active($bid);$this->authority($bid,$reason,$place);
  if($source&&$source->tempat_bertugas_id!==$place)$this->authority($bid,$reason,$source->tempat_bertugas_id);
  $def=$this->rental($os)?new DefinisiSewaanFotostat($bid->kitaran->tahun):new DefinisiBacaanMeter($bid->kitaran->tahun);
  abort_unless($def->readable($data),422,'Data Fotostat tidak sah.');
  if(!$this->internal&&$source&&$this->meter($os)){
   $link=DB::table('pautan_fotostat')->where('meter_id',$source->id)->first();
   if($link){$parent=ButiranAnggaran::findOrFail($link->sewaan_id);$this->sameContext($bid,$parent,$source);
    abort_unless($place===$parent->tempat_bertugas_id,422,'Tempat Bertugas dikawal oleh butiran sewaan.');
    foreach(self::SHARED as $key)abort_unless(($data->$key??null)===($parent->data_sumber->$key??null),422,'Butiran bersama dikawal oleh sewaan.');
   }
  }
 }
 private function sameContext(Bidaan $bid,ButiranAnggaran $r,ButiranAnggaran $m): void {
  [$rental,$meter]=$this->active($bid);
  abort_unless($r->anggaran_id===$rental->id&&$m->anggaran_id===$meter->id&&$r->ptj_id===$bid->ptj_id&&$m->ptj_id===$bid->ptj_id,409,'Pautan Fotostat tidak sepadan dengan Bidaan.');
 }
 private function internal(callable $fn): mixed {$old=$this->internal;$this->internal=true;try{return $fn();}finally{$this->internal=$old;}}
 private function write(Bidaan $bid,Anggaran $estimate,?ButiranAnggaran $source,stdClass $data,int $place,?string $reason): ButiranAnggaran {
  return $this->internal(fn()=>app(DrafButiranAnggaran::class)->simpan($estimate->id,$source?->id,[
   'semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$estimate->fresh()->semakan,
   ...($source?['semakan_butiran'=>$source->semakan]:[]),'tempat_bertugas_id'=>$place,'data_sumber'=>$data,'reason'=>$reason]));
 }
 private function event(Bidaan $bid,ButiranAnggaran $r,?int $meter,string $action,callable $fn): void {
  $this->audit->jalankan(Peranan::PenyediaAnggaran,function()use($bid,$r,$meter,$action,$fn){$fn();return new PeristiwaAudit($action,ButiranAnggaran::class,(string)$r->id,
   ['tempat_bertugas_id'=>$r->tempat_bertugas_id],['bidaan_id'=>$bid->id,'anggaran_id'=>$r->anggaran_id,'tempat_bertugas_id'=>$r->tempat_bertugas_id,'meter_id'=>$meter],
   'Pautan Sewaan Fotostat dan Bacaan Meter.',ptjId:$bid->ptj_id,aktivitiId:$bid->aktiviti_kewangan_id,tempatBertugasId:$r->tempat_bertugas_id,kitaranId:$bid->kitaran_id);});
 }
 public function afterSave(ButiranAnggaran $source,?string $reason): void {
  $estimate=$source->anggaran;if(!$this->rental($estimate->rujukanOs))return;
  $bid=$estimate->bidaan;[, $meter]=$this->active($bid);
  $link=DB::table('pautan_fotostat')->where('sewaan_id',$source->id)->lockForUpdate()->first();
  $target=$link?ButiranAnggaran::whereKey($link->meter_id)->lockForUpdate()->firstOrFail():null;
  if($target)$this->sameContext($bid,$source,$target);
  $data=$target?clone $target->data_sumber:(new DefinisiBacaanMeter($bid->kitaran->tahun))->source([]);
  foreach(self::SHARED as $key)$data->$key=$source->data_sumber->$key??null;
  $target=$this->write($bid,$meter,$target,$data,$source->tempat_bertugas_id,$reason);
  if(!$link)$this->event($bid,$source,$target->id,'source.meter_linked',fn()=>DB::table('pautan_fotostat')->insert(['sewaan_id'=>$source->id,'meter_id'=>$target->id,'created_at'=>now(),'updated_at'=>now()]));
 }
 public function beforeCopy(Bidaan $bid,Anggaran $estimate,ButiranAnggaran $source,?string $reason): void {
  $os=$estimate->rujukanOs;if(!$this->rental($os)&&!$this->meter($os))return;
  $this->active($bid);$this->authority($bid,$reason,$source->tempat_bertugas_id);
  abort_if($this->meter($os)&&DB::table('pautan_fotostat')->where('meter_id',$source->id)->exists(),409,'Salin butiran melalui Sewaan Fotostat.');
 }
 public function beforeDelete(Bidaan $bid,Anggaran $estimate,ButiranAnggaran $source,array $input): void {
  $os=$estimate->rujukanOs;if(!$this->rental($os)&&!$this->meter($os))return;
  $this->active($bid);$this->authority($bid,$input['reason']??null,$source->tempat_bertugas_id);
  if($this->meter($os))abort_if(DB::table('pautan_fotostat')->where('meter_id',$source->id)->exists(),409,'Padam butiran melalui Sewaan Fotostat.');
  $link=DB::table('pautan_fotostat')->where('sewaan_id',$source->id)->lockForUpdate()->first();if(!$link)return;
  $target=ButiranAnggaran::whereKey($link->meter_id)->lockForUpdate()->firstOrFail();$this->sameContext($bid,$source,$target);
  abort_unless(($input['sahkan_meter']??false)===true&&($input['semakan_meter']??-1)===$target->semakan,409,'Semak dan sahkan nilai Bacaan Meter sebelum memadam pasangan.');
  $this->authority($bid,$input['reason']??null,$target->tempat_bertugas_id);
  $this->event($bid,$source,$target->id,'source.meter_unlinked',fn()=>DB::table('pautan_fotostat')->where('id',$link->id)->delete());
  app(DrafButiranAnggaran::class)->padam($target->anggaran_id,$target->id,['semakan_bidaan'=>$bid->fresh()->semakan,'semakan_anggaran'=>$target->anggaran->fresh()->semakan,'semakan_butiran'=>$target->semakan,'reason'=>$input['reason']??null]);
 }
 public function attach(int $rentalId,int $meterId,array $input): void {
  DB::transaction(function()use($rentalId,$meterId,$input){
   $this->guard->actor();$context=ButiranAnggaran::findOrFail($rentalId);$bid=$context->anggaran->bidaan;
   $this->guard->prepareLocks($bid->kitaran_id);$bid=Bidaan::whereKey($bid->id)->lockForUpdate()->firstOrFail();
   $this->guard->current($bid,(int)$input['semakan_bidaan'],[StatusBidaan::DalamPenyediaan,StatusBidaan::Dikembalikan]);$this->authority($bid,$input['reason']??null);
   $r=ButiranAnggaran::whereKey($rentalId)->lockForUpdate()->firstOrFail();$m=ButiranAnggaran::whereKey($meterId)->lockForUpdate()->firstOrFail();$this->sameContext($bid,$r,$m);
   abort_unless($r->semakan===(int)$input['semakan_sewaan']&&$m->semakan===(int)$input['semakan_meter']&&($input['sahkan']??false)===true,409);
   $this->authority($bid,$input['reason']??null,$r->tempat_bertugas_id);$this->authority($bid,$input['reason']??null,$m->tempat_bertugas_id);
   abort_if(DB::table('pautan_fotostat')->where('meter_id',$m->id)->exists(),409,'Bacaan Meter sudah dipautkan.');
   abort_unless((new DefinisiSewaanFotostat($bid->kitaran->tahun))->readable($r->data_sumber)&&(new DefinisiBacaanMeter($bid->kitaran->tahun))->readable($m->data_sumber),409);
   $link=DB::table('pautan_fotostat')->where('sewaan_id',$r->id)->lockForUpdate()->first();
   if($link){$old=ButiranAnggaran::whereKey($link->meter_id)->lockForUpdate()->firstOrFail();$this->sameContext($bid,$r,$old);
    foreach(self::FINANCIAL as $key)abort_unless(($old->data_sumber->$key??null)===null||$old->data_sumber->$key==='',409,'Bacaan Meter terpaut sudah berisi; pautan tidak diganti.');
    $this->beforeDelete($bid,$r->anggaran,$r,['sahkan_meter'=>true,'semakan_meter'=>$old->semakan,'reason'=>$input['reason']??null]);
   }
   $data=clone $m->data_sumber;foreach(self::SHARED as $key)$data->$key=$r->data_sumber->$key??null;
   [, $estimate]=$this->active($bid);$m=$this->write($bid,$estimate,$m,$data,$r->tempat_bertugas_id,$input['reason']??null);
   $this->event($bid,$r,$m->id,'source.meter_linked',fn()=>DB::table('pautan_fotostat')->insert(['sewaan_id'=>$r->id,'meter_id'=>$m->id,'created_at'=>now(),'updated_at'=>now()]));
   // Linking an otherwise identical draft must still stale all existing editor forms.
   DB::table('bidaan')->where('id',$bid->id)->increment('semakan');
  });
 }
}
