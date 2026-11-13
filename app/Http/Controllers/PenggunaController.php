<?php

namespace App\Http\Controllers;

use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\Ptj;
use App\Models\TempatBertugas;
use App\Models\User;
use App\Services\PengurusanPengguna;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PenggunaController extends Controller
{
    public function index(Request $request, PengurusanPengguna $service)
    {
        abort_unless($service->bolehUrus($request->user()), 403);
        $request->validate(['q' => 'nullable|string|max:255']);
        $search = trim((string) $request->query('q'));
        $users = User::with(['ptj', 'tempatBertugas', 'penugasanPeranan.ptj', 'penugasanPeranan.aktivitiKewangan', 'penugasanPeranan.tempatBertugas'])->when($search !== '', function ($query) use ($search) {
            $query->where(fn ($q) => $q->where('name', 'ilike', '%'.$search.'%')->orWhere('email', 'ilike', '%'.$search.'%'));
        })->orderBy('name')->orderBy('id')->paginate(25)->withQueryString();
        return view('abm.users.index', compact('users', 'search'));
    }

    public function form(Request $request, PengurusanPengguna $service, ?User $user = null)
    {
        abort_unless($service->bolehUrus($request->user()), 403);
        $user ??= new User(['name' => '', 'email' => '']);
        $user->load('penugasanPeranan');
        $roles = [];
        foreach ($user->penugasanPeranan->groupBy(fn ($row) => $row->peranan->value) as $key => $rows) {
            $roles[$key] = ['enabled' => '1', 'rows' => $key === 'superadmin' ? [] : $rows->map(fn ($row) => [
                'selected' => '1', 'ptj_id' => $row->ptj_id, 'aktiviti_kewangan_id' => $row->aktiviti_kewangan_id,
                'tempat_bertugas_id' => $row->tempat_bertugas_id,
            ])->all()];
        }
        return view('abm.users.form', [
            'account' => $user, 'roles' => $roles, 'roleCases' => Peranan::cases(),
            'ptjs' => Ptj::orderBy('nama')->get(),
            'activities' => AktivitiKewangan::orderBy('nama')->get(),
            'workplaces' => TempatBertugas::with('ptj')->orderBy('nama')->get(),
        ]);
    }

    public function save(Request $request, PengurusanPengguna $service, ?User $user = null)
    {
        abort_unless($service->bolehUrus($request->user()), 403);
        try {
            $saved = $service->simpan($user?->id, $request->all() + ['roles' => []]);
        } catch (QueryException $exception) {
            Log::notice('abm.access.save_failed', ['exception_type' => get_class($exception), 'sql_state' => $exception->getCode()]);
            return back()->withInput($request->only(['name', 'email', 'is_active', 'ptj_id', 'tempat_bertugas_id', 'access_revision', 'reason', 'roles']))->withErrors(['save' => 'Perubahan tidak disimpan. Semak e-mel pendua atau muat semula dan cuba lagi.']);
        }
        if ($saved->id === $request->user()->id) {
            return $this->signedOut($request, 'Perubahan disimpan. Sila log masuk semula.');
        }
        return redirect()->route('users.edit', $saved)->with('status', 'Maklumat dan penugasan disimpan. Sesi terdahulu telah ditamatkan.');
    }

    public function revoke(Request $request, User $user, PengurusanPengguna $service)
    {
        $data = $request->validate(['access_revision' => 'required|integer|min:0', 'reason' => 'required|string|max:1000']);
        $service->tamatkanSesi($user->id, (int) $data['access_revision'], $data['reason']);
        if ($user->id === $request->user()->id) { return $this->signedOut($request, 'Semua sesi ABM anda telah ditamatkan.'); }
        return redirect()->route('users.edit', $user)->with('status', 'Semua sesi ABM pengguna telah ditamatkan.');
    }

    public function logoutAll(Request $request, PengurusanPengguna $service)
    {
        $user = $request->user();
        $service->tamatkanSesi($user->id, $user->access_revision, 'Pengguna memilih Log Keluar Semua Peranti.', true);
        return $this->signedOut($request, 'Semua sesi ABM anda telah ditamatkan.');
    }

    private function signedOut(Request $request, string $message)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->withErrors(['login' => $message]);
    }
}
