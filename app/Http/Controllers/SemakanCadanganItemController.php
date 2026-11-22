<?php

namespace App\Http\Controllers;

use App\Models\CadanganItemAnggaran;
use App\Services\RuangBidaPeruntukan;
use App\Services\UrusCadanganItem;
use Illuminate\Http\Request;

class SemakanCadanganItemController extends Controller
{
    public function index(Request $request, RuangBidaPeruntukan $workspace)
    {
        abort_unless($workspace->admin($request->user()), 403);

        return view('abm.estimates.semakan-cadangan', [
            'proposals' => CadanganItemAnggaran::with(['pemohon', 'ptj'])
                ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 ELSE 1 END")
                ->latest('id')->paginate(20),
        ]);
    }

    public function decide(Request $request, int $proposal, UrusCadanganItem $items)
    {
        $items->decide($proposal, $request->only(['status', 'kadar', 'catatan']));

        return redirect()->route('item-proposals.index')->with('status', 'Keputusan cadangan disimpan.');
    }
}
