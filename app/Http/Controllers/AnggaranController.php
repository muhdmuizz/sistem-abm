<?php

namespace App\Http\Controllers;

use App\Models\Bidaan;
use App\Services\RuangAnggaran;
use App\Services\RuangBidaPeruntukan;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AnggaranController extends Controller
{
    private function context(Request $request, int $bid, RuangBidaPeruntukan $workspace): Bidaan
    {
        $record = Bidaan::findOrFail($bid);
        abort_unless($workspace->canView($request->user(), $record->ptj_id, $record->aktiviti_kewangan_id), 403);
        return $record->load(['ptj', 'aktivitiKewangan', 'kitaran']);
    }

    private function navigation(Request $request, Bidaan $bid, RuangBidaPeruntukan $workspace): Collection
    {
        $catalogue = $workspace->catalogue($request->user(), $bid);
        $estimates = $bid->anggaran()->whereIn('rujukan_os_id', $catalogue->pluck('id'))->get()->keyBy('rujukan_os_id');
        return $catalogue->map(function ($os) use ($request, $bid, $workspace, $estimates) {
            $estimate = $estimates->get($os->id);
            $active = (bool) $estimate?->diaktifkan;
            if ($active) {
                $url = route('estimates.show', [$bid->id, $estimate->id]);
            } else {
                $activation = $workspace->activation($request->user(), $bid->kitaran_id, $bid->ptj_id,
                    $bid->aktiviti_kewangan_id, $bid, $os, $estimate);
                $url = $activation['allowed'] ? route('bids.os-form', [$bid->id, $os->id])
                    : route('bids.show', $bid->id).'#os-'.$os->id;
            }
            return ['os' => $os, 'active' => $active, 'url' => $url];
        })->values();
    }

    public function show(Request $request, int $bid, int $estimate, RuangBidaPeruntukan $workspace, RuangAnggaran $rows)
    {
        $record = $this->context($request, $bid, $workspace);
        $estimateRecord = $record->anggaran()->whereKey($estimate)->firstOrFail();
        $navigation = $this->navigation($request, $record, $workspace);
        $index = $navigation->search(fn ($item) => $item['os']->id === $estimateRecord->rujukan_os_id);
        abort_if($index === false, 404);
        abort_unless($estimateRecord->diaktifkan, 409, 'OS belum aktif. Kembali ke senarai OS untuk menyemak pengaktifan.');
        $sources = $rows->sources($request->user(), $record, $estimateRecord)
            ->orderByDesc('updated_at')->orderByDesc('id')->paginate(25)->withQueryString();
        return view('abm.estimates.show', ['bid' => $record, 'estimate' => $estimateRecord,
            'os' => $navigation[$index]['os'], 'navigation' => $navigation, 'sources' => $sources,
            'previous' => $index > 0 ? $navigation[$index - 1] : null,
            'next' => $navigation->get($index + 1),
            'open' => $workspace->open($record->kitaran_id, $record->ptj_id, $record->aktiviti_kewangan_id)]);
    }

    public function navigate(Request $request, int $bid, RuangBidaPeruntukan $workspace)
    {
        $record = $this->context($request, $bid, $workspace);
        $data = $request->validate(['os_id' => 'required|integer|min:1']);
        $target = $this->navigation($request, $record, $workspace)->first(fn ($item) => $item['os']->id === (int) $data['os_id']);
        abort_unless($target, 404);
        // Only server-generated, freshly scoped destinations; GET never activates an OS.
        return redirect($target['url']);
    }
}
