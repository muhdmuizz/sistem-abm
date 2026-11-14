<?php

namespace App\Http\Controllers;

use App\Models\AktivitiKewangan;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Services\PengurusanKitaran;
use App\Services\PengurusanPengguna;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KitaranController extends Controller
{
    private function authorizeAdmin(Request $request, PengurusanPengguna $users): void
    {
        abort_unless($users->bolehUrus($request->user()), 403);
    }

    public function index(Request $request, PengurusanPengguna $users)
    {
        $this->authorizeAdmin($request, $users);
        $data = $request->validate(['tahun' => 'nullable|integer|between:1,9999']);
        $year = $data['tahun'] ?? null;
        $cycles = Kitaran::withCount('fasa')->with(['fasa' => fn ($q) => $q->orderByDesc('id'),
            'fasa.skop.ptj', 'fasa.skop.aktivitiKewangan'])
            ->when($year, fn ($q) => $q->where('tahun', $year))->orderByDesc('tahun')->paginate(25)->withQueryString();
        return view('abm.cycles.index', compact('cycles', 'year'));
    }

    public function create(Request $request, PengurusanPengguna $users)
    {
        $this->authorizeAdmin($request, $users);
        return view('abm.cycles.create');
    }

    public function store(Request $request, PengurusanPengguna $users, PengurusanKitaran $service)
    {
        $this->authorizeAdmin($request, $users);
        try { $cycle = $service->ciptaKitaran($request->all()); }
        catch (QueryException $exception) {
            Log::notice('abm.cycle.save_failed', ['exception_type' => get_class($exception)]);
            return back()->withInput($request->only('tahun'))->withErrors(['tahun' => 'Kitaran tidak disimpan. Semak tahun pendua atau muat semula dan cuba lagi.']);
        }
        return redirect()->route('cycles.index', ['tahun' => $cycle->tahun])->with('status', 'Kitaran ABM ditambah.');
    }

    public function phaseForm(Request $request, int $cycle, PengurusanPengguna $users, ?int $phase = null)
    {
        $this->authorizeAdmin($request, $users);
        $record = Kitaran::findOrFail($cycle);
        $fasa = $phase === null ? null : $record->fasa()->with('skop')->whereKey($phase)->firstOrFail();
        return view('abm.cycles.phase-form', ['cycle' => $record, 'phase' => $fasa,
            'ptjs' => Ptj::orderBy('nama')->get(), 'activities' => AktivitiKewangan::orderBy('nama')->get(),
            'scopeTypes' => PengurusanKitaran::SKOP]);
    }

    public function phaseSave(Request $request, int $cycle, PengurusanPengguna $users, PengurusanKitaran $service, ?int $phase = null)
    {
        $this->authorizeAdmin($request, $users);
        try { $saved = $service->simpanFasa($cycle, $phase, $request->all()); }
        catch (QueryException $exception) {
            Log::notice('abm.phase.save_failed', ['exception_type' => get_class($exception)]);
            return back()->withInput($request->only(['phase_context', 'jenis', 'tarikh_mula', 'tarikh_tamat',
                'jenis_skop', 'ptj_ids', 'aktiviti_ids', 'pasangan', 'semakan', 'reason']))
                ->withErrors(['save' => 'Fasa tidak disimpan. Muat semula dan semak pilihan rujukan sebelum mencuba lagi.']);
        }
        return redirect()->route('cycles.index', ['tahun' => $saved->tahun_kitaran])->with('status', 'Maklumat Fasa disimpan. Pembukaan dikawal melalui tindakan berasingan.');
    }

    public function actionForm(Request $request, int $cycle, int $phase, string $action, PengurusanPengguna $users)
    {
        $this->authorizeAdmin($request, $users);
        abort_unless(in_array($action, ['buka', 'tutup', 'buka-semula', 'lanjutkan'], true), 404);
        $record = Kitaran::findOrFail($cycle);
        $fasa = $record->fasa()->with(['skop.ptj', 'skop.aktivitiKewangan'])->whereKey($phase)->firstOrFail();
        $valid = match ($action) {
            'buka' => ! $fasa->dibuka && ! $fasa->pernah_dibuka,
            'buka-semula' => ! $fasa->dibuka && $fasa->pernah_dibuka,
            'tutup' => $fasa->dibuka,
            'lanjutkan' => true,
        };
        abort_unless($valid, 409, 'Tindakan tidak lagi sesuai. Muat semula senarai Fasa.');
        return view('abm.cycles.action', ['cycle' => $record, 'phase' => $fasa, 'action' => $action]);
    }

    public function actionSave(Request $request, int $cycle, int $phase, string $action, PengurusanPengguna $users, PengurusanKitaran $service)
    {
        $this->authorizeAdmin($request, $users);
        try { $saved = $service->tindakan($cycle, $phase, $action, $request->all()); }
        catch (QueryException $exception) {
            Log::notice('abm.phase.action_failed', ['exception_type' => get_class($exception)]);
            return back()->withInput($request->only(['action_context', 'semakan', 'reason', 'tarikh_tamat']))
                ->withErrors(['save' => 'Tindakan tidak disimpan. Muat semula dan cuba lagi.']);
        }
        return redirect()->route('cycles.index', ['tahun' => $saved->tahun_kitaran])->with('status', 'Tindakan Fasa disimpan dan direkod dalam jejak audit.');
    }
}
