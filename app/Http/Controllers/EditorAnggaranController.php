<?php

namespace App\Http\Controllers;

use App\Models\Bidaan;
use App\Services\KatalogDefinisiAnggaran;
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
        $definition=$os ? app(KatalogDefinisiAnggaran::class)->untuk($os,$record) : null;
        abort_unless($definition,404);
        abort_unless($entry->diaktifkan,409);
        return [$record,$entry,$os,$definition];
    }

    public function show(Request $request,int $bid,int $estimate,EditorAnggaran $editor,RuangAnggaran $scope)
    {
        [$record,$entry,$os,$definition]=$this->context($request,$bid,$estimate);
        $request->validate(['page'=>'nullable|integer|min:1']);
        $sources=$scope->sources($request->user(),$record,$entry)->addSelect(['semakan','data_sumber'])
            ->orderBy('id')->paginate(25)->withQueryString();
        $access=$editor->access($request->user(),$record,$entry);
        $visible=$sources->map(function($source) use($definition,$access) {
            $valid=$definition->readable($source->data_sumber);
            return ['butiran_id'=>$source->id,'semakan_butiran'=>$source->semakan,
                'tempat_bertugas_id'=>$source->tempat_bertugas_id,'tempat_bertugas'=>$source->tempatBertugas->nama,
                ...($valid ? $definition->project($source->data_sumber) : []),
                'preview'=>$valid ? $definition->preview($source->data_sumber) : null,
                'editable'=>$valid && $access['editable'] && $access['places']->contains('id',$source->tempat_bertugas_id),
                'compatible'=>$valid];
        })->values();
        return view('abm.estimates.editor',['bid'=>$record,'estimate'=>$entry,'os'=>$os,'sources'=>$sources,
            'records'=>$visible,'references'=>$definition->references(),'columns'=>$definition->columns(),'referenceSelector'=>$definition->selector(),'inputHelp'=>$definition->inputHelp(),'inputLabels'=>array_column(array_filter($definition->columns(),fn($c)=>$c['type']!=='output'),'label')] + $access);
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
