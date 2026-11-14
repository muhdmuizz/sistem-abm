<?php

namespace App\Http\Controllers;

use App\Models\Bidaan;
use App\Services\DefinisiAnggaran;
use App\Services\EditorAnggaran;
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
        abort_unless($os && app(DefinisiAnggaran::class)->supports($os),404);
        abort_unless($entry->diaktifkan,409);
        return [$record,$entry,$os];
    }

    public function show(Request $request,int $bid,int $estimate,EditorAnggaran $editor,DefinisiAnggaran $definition,RuangAnggaran $scope)
    {
        [$record,$entry,$os]=$this->context($request,$bid,$estimate);
        $request->validate(['page'=>'nullable|integer|min:1']);
        $sources=$scope->sources($request->user(),$record,$entry)->addSelect(['semakan','data_sumber'])
            ->orderBy('id')->paginate(25)->withQueryString();
        $access=$editor->access($request->user(),$record,$entry);
        $visible=$sources->map(function($source) use($definition,$access) {
            $valid=$definition->readable($source->data_sumber);
            return ['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan,
                'tempat_bertugas_id'=>$source->tempat_bertugas_id,'tempat_bertugas'=>$source->tempatBertugas->nama,
                'kategori1'=>$valid ? $source->data_sumber->kategori1 : null,
                'bil_unit'=>$valid ? $source->data_sumber->bil_unit : null,
                'preview'=>$valid ? $definition->preview($source->data_sumber) : null,
                'editable'=>$valid && $access['editable'] && $access['places']->contains('id',$source->tempat_bertugas_id),
                'compatible'=>$valid];
        })->values();
        return view('abm.estimates.editor',['bid'=>$record,'estimate'=>$entry,'os'=>$os,'sources'=>$sources,
            'records'=>$visible,'references'=>$definition->references()] + $access);
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
