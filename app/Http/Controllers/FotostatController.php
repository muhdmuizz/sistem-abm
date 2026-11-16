<?php
namespace App\Http\Controllers;
use App\Models\Bidaan;
use App\Services\{PautanFotostat,RuangBidaPeruntukan,RuangAnggaran,EditorAnggaran,PengaktifanKewangan};
use Illuminate\Http\Request;
class FotostatController extends Controller {
 private function context(Request $request,int $bid): array {
  $record=Bidaan::with(['kitaran','ptj','aktivitiKewangan'])->findOrFail($bid);$workspace=app(RuangBidaPeruntukan::class);
  abort_unless($workspace->canView($request->user(),$record->ptj_id,$record->aktiviti_kewangan_id),403);
  $links=app(PautanFotostat::class);[$r,$m]=$links->pair($record);$visible=$workspace->catalogue($request->user(),$record);
  abort_unless($visible->contains('id',$r->id)&&$visible->contains('id',$m->id),403);
  [$rental,$meter]=$links->active($record);
  $a=app(EditorAnggaran::class)->access($request->user(),$record,$rental);$b=app(EditorAnggaran::class)->access($request->user(),$record,$meter);
  abort_unless($a['editable']&&$b['editable'],403);
  return ['bid'=>$record,'rental'=>$rental,'meter'=>$meter,'needsReason'=>$a['needsReason']||$b['needsReason']];
 }
 public function links(Request $request,int $bid) {
  $context=$this->context($request,$bid);$scope=app(RuangAnggaran::class);$record=$context['bid'];
  $rentals=$scope->sources($request->user(),$record,$context['rental'])->addSelect(['data_sumber','semakan'])->orderBy('id')->get();
  $meters=$scope->sources($request->user(),$record,$context['meter'])->addSelect(['data_sumber','semakan'])->whereNotIn('id',\Illuminate\Support\Facades\DB::table('pautan_fotostat')->select('meter_id'))->orderBy('id')->get();
  $data=$request->validate(['sewaan_id'=>'nullable|integer|min:1','meter_id'=>'nullable|integer|min:1']);
  $r=isset($data['sewaan_id'])?$rentals->firstWhere('id',(int)$data['sewaan_id']):null;
  $m=isset($data['meter_id'])?$meters->firstWhere('id',(int)$data['meter_id']):null;
  abort_if(isset($data['sewaan_id'])&&!$r,404);abort_if(isset($data['meter_id'])&&!$m,404);
  $old=null;if($r){$id=\Illuminate\Support\Facades\DB::table('pautan_fotostat')->where('sewaan_id',$r->id)->value('meter_id');$old=$id?\App\Models\ButiranAnggaran::findOrFail($id):null;
   abort_if($old&&($old->anggaran_id!==$context['meter']->id||$old->tempat_bertugas_id!==$r->tempat_bertugas_id),409);
  }
  return view('abm.estimates.fotostat-links',$context+compact('rentals','meters','r','m','old'));
 }
 public function attach(Request $request,int $bid) {
  $this->context($request,$bid);
  $data=$request->validate(['sewaan_id'=>'required|integer|min:1','meter_id'=>'required|integer|min:1','semakan_bidaan'=>'required|integer|min:0','semakan_sewaan'=>'required|integer|min:0','semakan_meter'=>'required|integer|min:0','sahkan'=>'required|accepted','reason'=>'nullable|string|max:1000']);
  $record=Bidaan::findOrFail($bid);$scope=app(RuangAnggaran::class);[$rental,$meter]=app(PautanFotostat::class)->active($record);
  $scope->sources($request->user(),$record,$rental)->whereKey($data['sewaan_id'])->firstOrFail();
  $scope->sources($request->user(),$record,$meter)->whereKey($data['meter_id'])->firstOrFail();
  $data['sahkan']=true;app(PautanFotostat::class)->attach((int)$data['sewaan_id'],(int)$data['meter_id'],$data);
  return redirect()->route('fotostat.links',$bid)->with('status','Bacaan Meter dipautkan. Nilai kewangan dikekalkan.');
 }
 public function deactivateForm(Request $request,int $bid) {return view('abm.estimates.fotostat-deactivate',$this->context($request,$bid));}
 public function deactivate(Request $request,int $bid) {
  $this->context($request,$bid);$data=$request->validate(['semakan_bidaan'=>'required|integer|min:0','reason'=>'nullable|string|max:1000','sahkan'=>'required|accepted']);
  app(PengaktifanKewangan::class)->nyahaktifkanFotostat($bid,(int)$data['semakan_bidaan'],$data['reason']??null);
  return redirect()->route('bids.show',$bid)->with('status','Pasangan Fotostat dinyahaktifkan. Semua draf dikekalkan.');
 }
}
