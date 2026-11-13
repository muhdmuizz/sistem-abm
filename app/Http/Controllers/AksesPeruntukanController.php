<?php

namespace App\Http\Controllers;

use App\Models\AktivitiKewangan;
use App\Models\Ptj;
use App\Models\TetapanAksesPtj;
use App\Services\PengurusanAksesPeruntukan;
use App\Services\PengurusanPengguna;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AksesPeruntukanController extends Controller
{
    public function index(Request $request, PengurusanPengguna $users)
    {
        abort_unless($users->bolehUrus($request->user()), 403);
        $request->validate(['q' => 'nullable|string|max:255']);
        $search = trim((string) $request->query('q'));
        $ptjs = Ptj::when($search !== '', fn ($q) => $q->where(fn ($row) =>
            $row->where('nama', 'ilike', '%'.$search.'%')->orWhere('kod_ptj', 'ilike', '%'.$search.'%')))
            ->orderBy('nama')->orderBy('id')->paginate(25)->withQueryString();
        $overrides = TetapanAksesPtj::whereIn('ptj_id', $ptjs->pluck('id'))->get()->groupBy('ptj_id');
        return view('abm.permissions.index', ['ptjs' => $ptjs, 'search' => $search, 'overrides' => $overrides,
            'categories' => PengurusanAksesPeruntukan::KATEGORI]);
    }

    public function edit(Request $request, string $kind, string $key, PengurusanPengguna $users, PengurusanAksesPeruntukan $service)
    {
        abort_unless($users->bolehUrus($request->user()), 403);
        $target = $service->sasaran($kind, $key);
        $states = AktivitiKewangan::orderBy('nama')->get()->map(fn ($activity) => $service->keadaan($target, $activity));
        return view('abm.permissions.edit', compact('target', 'states'));
    }

    public function save(Request $request, string $kind, string $key, int $activity, PengurusanPengguna $users, PengurusanAksesPeruntukan $service)
    {
        abort_unless($users->bolehUrus($request->user()), 403);
        try {
            $service->simpan($kind, $key, $activity, $request->all() + ['settings' => []]);
        } catch (QueryException $exception) {
            Log::notice('abm.permissions.save_failed', ['exception_type' => get_class($exception)]);
            return back()->withInput($request->only(['permission_target', 'permission_activity_id', 'revision', 'reason', 'activity', 'settings']))
                ->withErrors(['save' => 'Tetapan tidak disimpan. Muat semula dan cuba lagi.']);
        }
        return redirect()->route('permissions.edit', [$kind, $key])->with('status', 'Tetapan Aktiviti Kewangan dan OS disimpan.');
    }
}
