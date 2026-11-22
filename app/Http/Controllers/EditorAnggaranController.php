<?php

namespace App\Http\Controllers;

use App\Models\Bidaan;
use App\Services\KatalogDefinisiAnggaran;
use App\Services\EditorAnggaran;
use App\Services\DefinisiAlatPejabat;
use App\Services\DefinisiBahanMentah;
use App\Services\KelengkapanAnggaran;
use App\Services\RuangAnggaran;
use App\Services\RuangBidaPeruntukan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EditorAnggaranController extends Controller
{
    private function context(Request $request,int $bid,int $estimate): array
    {
        $record=Bidaan::with(['ptj','aktivitiKewangan','kitaran'])->findOrFail($bid);
        $workspace=app(RuangBidaPeruntukan::class);
        abort_unless($workspace->canView($request->user(),$record->ptj_id,$record->aktiviti_kewangan_id),403);
        $entry=$record->anggaran()->whereKey($estimate)->firstOrFail();
        $os=$workspace->catalogue($request->user(),$record)->firstWhere('id',$entry->rujukan_os_id);
        $definition=$os ? app(KatalogDefinisiAnggaran::class)->untuk($os,$record) : null;
        abort_unless($definition,404);
        abort_unless($entry->diaktifkan,409);
        return [$record,$entry,$os,$definition];
    }

    public function show(Request $request,int $bid,int $estimate,EditorAnggaran $editor,RuangAnggaran $scope)
    {
        [$record,$entry,$os,$definition]=$this->context($request,$bid,$estimate);
        $request->validate(['page'=>'nullable|integer|min:1']);
        $query = $scope->sources($request->user(), $record, $entry)->addSelect(['semakan', 'data_sumber'])->orderBy('id');
        $sources = (clone $query)->paginate(25)->withQueryString();
        $references = $definition->references();
        $completeness = null;
        if ($definition instanceof DefinisiAlatPejabat || ($definition instanceof DefinisiBahanMentah && isset($references['Bukan Pukal']))) {
            $completeness = app(KelengkapanAnggaran::class)->ringkasan(
                $definition,
                $query->without('tempatBertugas')->cursor(),
                $references,
                $sources->pluck('id')->all(),
            );
        }
        $access=$editor->access($request->user(),$record,$entry);
        $links=app(\App\Services\PautanFotostat::class);
        $photocopier=$links->rental($os);$meter=$links->meter($os);$pairVisible=true;$pairActive=true;
        if($photocopier||$meter){
            [$r,$m]=$links->pair($record);$allowed=app(RuangBidaPeruntukan::class)->catalogue($request->user(),$record)->pluck('id');
            $pairVisible=$allowed->contains($r->id)&&$allowed->contains($m->id);
            $pairActive=$record->anggaran()->whereIn('rujukan_os_id',[$r->id,$m->id])->where('diaktifkan',true)->count()===2;
            $access['editable']=$access['editable']&&$pairVisible&&$pairActive;
        }
        $visible=$sources->map(function($source) use($definition,$access,$photocopier,$meter,$pairVisible,$record) {
            $meta=[];
            if($pairVisible&&($photocopier||$meter)){
                $link=\Illuminate\Support\Facades\DB::table('pautan_fotostat')->where($photocopier?'sewaan_id':'meter_id',$source->id)->first();
                if($link&&$photocopier){$target=\App\Models\ButiranAnggaran::findOrFail($link->meter_id);
                    abort_unless($target->anggaran->bidaan_id===$record->id&&$target->tempat_bertugas_id===$source->tempat_bertugas_id,409);
                    $meta['linked_meter']=['id'=>$target->id,'revision'=>$target->semakan,'summary'=>'Bacaan Meter #'.$target->id.': Salinan '.($target->data_sumber->bil_unit??'—').', Kadar '.($target->data_sumber->kadar??'—').', Bulan '.($target->data_sumber->bil_bulan??'—')];
                }
                if($link&&$meter){$parent=\App\Models\ButiranAnggaran::findOrFail($link->sewaan_id);
                    abort_unless($parent->anggaran->bidaan_id===$record->id&&$parent->tempat_bertugas_id===$source->tempat_bertugas_id,409);
                    $meta=['readonly_fields'=>['tempat_bertugas_id',...\App\Services\PautanFotostat::SHARED],'selectable'=>false,'origin_label'=>'Sewaan #'.$parent->id,'origin_url'=>route('estimates.editor',[$record->id,$parent->anggaran_id])];
                }
            }
            $valid=$definition->readable($source->data_sumber);
            return ['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan,
                'tempat_bertugas_id'=>$source->tempat_bertugas_id,'tempat_bertugas'=>$source->tempatBertugas->nama,
                ...($valid ? $definition->project($source->data_sumber) : []),
                'preview'=>$valid ? $definition->preview($source->data_sumber) : null,
                'editable'=>$valid && $access['editable'] && $access['places']->contains('id',$source->tempat_bertugas_id),
                'compatible'=>$valid,...$meta];
        })->values();
        return view(($definition instanceof \App\Services\DefinisiPakejBerkumpulan || $definition instanceof \App\Services\DefinisiUmumBerkumpulan) ? 'abm.estimates.pakej' : 'abm.estimates.editor',['photocopier'=>$photocopier,'dependentMeter'=>$meter,'pairActive'=>$pairActive,'pairVisible'=>$pairVisible,'groupLabel'=>$definition instanceof \App\Services\DefinisiUmumBerkumpulan ? 'Umum' : 'Pakej','bid'=>$record,'estimate'=>$entry,'os'=>$os,'sources'=>$sources,
            'canPropose'=>$access['editable'] && app(\App\Services\UrusCadanganItem::class)->categories($definition) !== [], 'records'=>$visible,'references'=>$references,'completeness'=>$completeness,'columns'=>$definition->displayColumns(),'referenceSelector'=>$definition->selector(),'referenceChildSelector'=>$definition->childSelector(),'inputHelp'=>$definition->inputHelp(),'inputLabels'=>array_column(array_filter($definition->displayColumns(),fn($c)=>$c['type']!=='output'),'label')] + $access);
    }

    public function cadangan(Request $request, int $bid, int $estimate, EditorAnggaran $editor, \App\Services\UrusCadanganItem $items)
    {
        [$record, $entry, $os, $definition] = $this->context($request, $bid, $estimate);
        $access = $editor->access($request->user(), $record, $entry);
        abort_unless($access['editable'], 403);
        $categories = array_keys($items->categories($definition));
        abort_if($categories === [], 404);

        return view('abm.estimates.cadangan', [
            'bid' => $record, 'estimate' => $entry, 'os' => $os, 'categories' => $categories,
            'needsReason' => $access['needsReason'],
            'proposals' => \App\Models\CadanganItemAnggaran::where('anggaran_id', $estimate)
                ->where('pemohon_id', $request->user()->id)->latest('id')->paginate(15),
        ]);
    }

    public function simpanCadangan(Request $request, int $bid, int $estimate, \App\Services\UrusCadanganItem $items)
    {
        $this->context($request, $bid, $estimate);
        $items->create($bid, $estimate, $request->only(['kategori', 'nama', 'unit', 'kadar', 'justifikasi', 'reason']));

        return redirect()->route('estimates.editor', [$bid, $estimate])
            ->with('status', 'Cadangan dihantar. Item sementara boleh dipilih dalam draf.');
    }

    public function save(Request $request,int $bid,int $estimate,EditorAnggaran $editor)
    {
        $this->context($request,$bid,$estimate);
        try { $editor->save($estimate,$request->except(['_token','_method'])); }
        catch(QueryException $exception) {
            Log::notice('abm.grid.save_failed',['exception_type'=>get_class($exception)]);
            return response()->json(['message'=>'Draf tidak disimpan. Perubahan anda dikekalkan; cuba lagi.'],503);
        }
        session()->flash('status','Draf Butiran Anggaran disimpan.');
        return response()->json(['url'=>route('estimates.editor',[$bid,$estimate])]);
    }
}
