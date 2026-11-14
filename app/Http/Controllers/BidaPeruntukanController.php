<?php

namespace App\Http\Controllers;

use App\Models\Bidaan;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Services\PengaktifanKewangan;
use App\Services\RuangBidaPeruntukan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BidaPeruntukanController extends Controller
{
    public function index(Request $request, RuangBidaPeruntukan $workspace)
    {
        abort_unless($workspace->canUse($request->user()), 403);
        $data = $request->validate(['kitaran_id' => 'nullable|integer|min:1', 'ptj_id' => 'nullable|integer|min:1']);
        $cycles = Kitaran::orderByDesc('tahun')->get();
        $ptjs = $workspace->ptjs($request->user());
        $cycle = isset($data['kitaran_id']) ? $cycles->firstWhere('id', (int) $data['kitaran_id']) : null;
        $ptj = isset($data['ptj_id']) ? $ptjs->firstWhere('id', (int) $data['ptj_id']) : null;
        abort_if(isset($data['kitaran_id']) && ! $cycle, 404);
        abort_if(isset($data['ptj_id']) && ! $ptj, 403);
        $cards = collect();
        if ($cycle && $ptj) {
            $activities = $workspace->activities($request->user(), $ptj);
            $bids = Bidaan::where('kitaran_id', $cycle->id)->where('ptj_id', $ptj->id)
                ->whereIn('aktiviti_kewangan_id', $activities->pluck('id'))->get()->keyBy('aktiviti_kewangan_id');
            $cards = $activities->map(fn ($activity) => ['activity' => $activity, 'bid' => $bids->get($activity->id),
                'activation' => $workspace->activation($request->user(), $cycle->id, $ptj->id, $activity->id, $bids->get($activity->id))]);
        }
        return view('abm.bids.index', compact('cycles', 'ptjs', 'cycle', 'ptj', 'cards'));
    }

    private function bidContext(Request $request, int $bid, RuangBidaPeruntukan $workspace): Bidaan
    {
        $record = Bidaan::findOrFail($bid);
        abort_unless($workspace->canView($request->user(), $record->ptj_id, $record->aktiviti_kewangan_id), 403);
        return $record->load(['ptj', 'aktivitiKewangan', 'kitaran']);
    }

    public function show(Request $request, int $bid, RuangBidaPeruntukan $workspace)
    {
        $record = $this->bidContext($request, $bid, $workspace);
        $catalogue = $workspace->catalogue($request->user(), $record);
        $estimates = $record->anggaran()->whereIn('rujukan_os_id', $catalogue->pluck('id'))->get()->keyBy('rujukan_os_id');
        $cards = $catalogue->map(fn ($os) => ['os' => $os, 'estimate' => $estimates->get($os->id),
            'activation' => $workspace->activation($request->user(), $record->kitaran_id, $record->ptj_id,
                $record->aktiviti_kewangan_id, $record, $os, $estimates->get($os->id))]);
        $open = $workspace->open($record->kitaran_id, $record->ptj_id, $record->aktiviti_kewangan_id);
        return view('abm.bids.show', ['bid' => $record, 'cards' => $cards, 'open' => $open]);
    }

    public function bidForm(Request $request, int $cycle, int $ptj, int $activity, RuangBidaPeruntukan $workspace)
    {
        $record = Kitaran::findOrFail($cycle);
        $owner = Ptj::findOrFail($ptj);
        $financialActivity = \App\Models\AktivitiKewangan::findOrFail($activity);
        abort_unless($workspace->canView($request->user(), $ptj, $activity), 403);
        $bid = Bidaan::where('kitaran_id', $cycle)->where('ptj_id', $ptj)->where('aktiviti_kewangan_id', $activity)->first();
        $activation = $workspace->activation($request->user(), $cycle, $ptj, $activity, $bid);
        abort_unless($activation['allowed'], 403);
        return view('abm.bids.activate', ['cycle' => $record, 'ptj' => $owner, 'activity' => $financialActivity,
            'bid' => $bid, 'os' => null, 'activation' => $activation, 'activationErrors' => $this->formErrors($request),
            'context' => 'bid/'.$cycle.'/'.$ptj.'/'.$activity,
            'postUrl' => route('bids.activate', [$cycle, $ptj, $activity]),
            'backUrl' => route('bids.index', ['kitaran_id' => $cycle, 'ptj_id' => $ptj])]);
    }

    public function osForm(Request $request, int $bid, int $os, RuangBidaPeruntukan $workspace)
    {
        $record = $this->bidContext($request, $bid, $workspace);
        $reference = $workspace->catalogue($request->user(), $record)->firstWhere('id', $os);
        abort_unless($reference, 404);
        $estimate = $record->anggaran()->where('rujukan_os_id', $os)->first();
        $activation = $workspace->activation($request->user(), $record->kitaran_id, $record->ptj_id,
            $record->aktiviti_kewangan_id, $record, $reference, $estimate);
        abort_unless($activation['allowed'], 403);
        return view('abm.bids.activate', ['cycle' => $record->kitaran, 'ptj' => $record->ptj,
            'activity' => $record->aktivitiKewangan, 'bid' => $record, 'os' => $reference,
            'activation' => $activation, 'context' => 'os/'.$bid.'/'.$os,
            'postUrl' => route('bids.os-activate', [$bid, $os]), 'backUrl' => route('bids.show', $bid)]);
    }

    private function failure(Request $request, string $message)
    {
        return back()->withInput($request->only(['activation_context', 'reason']))
            ->withErrors(['save' => $message]);
    }

    public function activateBid(Request $request, int $cycle, int $ptj, int $activity, RuangBidaPeruntukan $workspace, PengaktifanKewangan $service)
    {
        abort_unless($workspace->canView($request->user(), $ptj, $activity), 403);
        $data = $request->validate(['semakan' => 'required|integer|min:0', 'reason' => 'nullable|string|max:1000']);
        try {
            $bid = $service->aktifkanBidaan($cycle, $ptj, $activity, (int) $data['semakan'], $data['reason'] ?? null);
        } catch (QueryException $exception) {
            Log::notice('abm.bid.activation_failed', ['exception_type' => get_class($exception)]);
            return $this->failure($request, 'Bidaan tidak diaktifkan. Muat semula dan cuba lagi.');
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409) { throw $exception; }
            return $this->failure($request, 'Bidaan telah berubah. Muat semula dan semak status semasa sebelum meneruskan.');
        }
        return redirect()->route('bids.show', $bid->id)->with('status', 'Bidaan diaktifkan.');
    }

    public function activateOs(Request $request, int $bid, int $os, RuangBidaPeruntukan $workspace, PengaktifanKewangan $service)
    {
        $record = $this->bidContext($request, $bid, $workspace);
        abort_unless($workspace->catalogue($request->user(), $record)->contains('id', $os), 404);
        $data = $request->validate(['semakan' => 'required|integer|min:0', 'reason' => 'nullable|string|max:1000']);
        try {
            $service->aktifkanOs($bid, $os, (int) $data['semakan'], $data['reason'] ?? null);
        } catch (QueryException $exception) {
            Log::notice('abm.os.activation_failed', ['exception_type' => get_class($exception)]);
            return $this->failure($request, 'OS tidak diaktifkan. Muat semula dan cuba lagi.');
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409) { throw $exception; }
            return $this->failure($request, 'Bidaan atau OS telah berubah. Muat semula dan semak status semasa.');
        }
        return redirect()->route('bids.show', $bid)->with('status', 'OS diaktifkan.');
    }
}
