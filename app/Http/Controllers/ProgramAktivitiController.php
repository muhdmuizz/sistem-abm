<?php

namespace App\Http\Controllers;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\Bidaan;
use App\Models\TempatBertugas;
use App\Models\TerasStrategik;
use App\Services\DrafProgramAktiviti;
use App\Services\RuangBidaPeruntukan;
use App\Services\RuangProgramAktiviti;
use App\Services\SkopPeranan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProgramAktivitiController extends Controller
{
    private function context(Request $request, int $id): Bidaan
    {
        $bid = Bidaan::with(['ptj','aktivitiKewangan','kitaran'])->findOrFail($id);
        abort_unless(app(RuangBidaPeruntukan::class)->canView($request->user(), $bid->ptj_id, $bid->aktiviti_kewangan_id), 403);
        return $bid;
    }

    /** Presentation only. The transactional service independently rechecks all save permissions. */
    private function access(Request $request, Bidaan $bid): array
    {
        $actor = $request->user();
        $roles = app(SkopPeranan::class);
        $workspace = app(RuangBidaPeruntukan::class);
        $places = TempatBertugas::where('ptj_id', $bid->ptj_id);
        if (! $roles->meliputi($actor->id, Peranan::PenyediaAnggaran, $bid->ptj_id, $bid->aktiviti_kewangan_id)) {
            $own = $actor->tempat_bertugas_id;
            if ($own && $roles->meliputi($actor->id, Peranan::PenyediaAnggaran, $bid->ptj_id, $bid->aktiviti_kewangan_id, $own)) {
                $places->whereKey($own);
            } else { $places->whereRaw('1 = 0'); }
        }
        $places = $places->orderBy('nama')->get();
        $open = $workspace->open($bid->kitaran_id, $bid->ptj_id, $bid->aktiviti_kewangan_id);
        $editable = $places->isNotEmpty() && $bid->diaktifkan &&
            in_array($bid->status, [StatusBidaan::DalamPenyediaan, StatusBidaan::Dikembalikan], true) &&
            ($workspace->admin($actor) || $open);
        return ['places' => $places, 'editable' => $editable,
            'isSuperadmin' => $workspace->admin($actor),
            'needsReason' => $workspace->admin($actor) && (! $open || ! app(\App\Services\AksesPeruntukan::class)->bolehAksesAktiviti($bid->ptj_id, $bid->aktiviti_kewangan_id))];
    }

    private function fixedWorkplace(Request $request, array $access, ?int $existingId = null): ?TempatBertugas
    {
        if ($access['isSuperadmin']) {
            return null;
        }

        if ($existingId !== null) {
            return $access['places']->firstWhere('id', $existingId);
        }

        $own = $request->user()->tempat_bertugas_id;
        return ($own ? $access['places']->firstWhere('id', $own) : null)
            ?? ($access['places']->count() === 1 ? $access['places']->first() : null);
    }

    public function index(Request $request, int $bid, RuangProgramAktiviti $scope)
    {
        $record = $this->context($request, $bid);
        $request->validate(['page' => 'nullable|integer|min:1']);
        $programs = $scope->query($request->user(), $record)->with('tempatBertugas')->orderByDesc('id')->paginate(20);
        return view('abm.programs.index', ['bid' => $record, 'programs' => $programs] + $this->access($request, $record));
    }

    public function form(Request $request, int $bid, ?int $program = null)
    {
        $record = $this->context($request, $bid);
        $access = $this->access($request, $record);
        $entry = $program === null ? null : app(RuangProgramAktiviti::class)->query($request->user(), $record)
            ->with(['komponen','bilangan','tempatBertugas'])->whereKey($program)->firstOrFail();
        $editable = $access['editable'] && (! $entry || $access['places']->contains('id', $entry->tempat_bertugas_id));
        abort_if(! $entry && ! $editable, 403);
        return view('abm.programs.form', ['bid' => $record, 'program' => $entry,
            'objectives' => TerasStrategik::orderBy('nombor')->with(['strategi' => fn ($q) => $q->orderBy('nombor'),
                'strategi.prakarsa' => fn ($q) => $q->orderBy('nombor')])->get(),
            'editable' => $editable, 'fixedWorkplace' => $this->fixedWorkplace($request, $access, $entry?->tempat_bertugas_id)] + $access);
    }

    public function save(Request $request, int $bid, DrafProgramAktiviti $service, ?int $program = null)
    {
        $record = $this->context($request, $bid);
        // Resolve the scoped URL identity before processing user-supplied metadata.
        $entry = $program === null ? null : app(RuangProgramAktiviti::class)->query($request->user(), $record)->whereKey($program)->firstOrFail();
        $input = $request->except(['_token','_method']);
        $fixed = $this->fixedWorkplace($request, $this->access($request, $record), $entry?->tempat_bertugas_id);
        if ($fixed !== null) {
            $submitted = $input['tempat_bertugas_id'] ?? $fixed->id;
            abort_unless(is_scalar($submitted) && (string) $submitted === (string) $fixed->id, 403);
            $input['tempat_bertugas_id'] = $fixed->id;
        }
        // Unchecked/removed child rows are an explicit empty replacement, never a patch.
        if (isset($input['maklumat']) && is_array($input['maklumat'])) {
            foreach (['komponen','jemputan','peserta'] as $group) { $input['maklumat'][$group] ??= []; }
        }
        try {
            $saved = $service->simpan($bid, $program, $input);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        } catch (QueryException $exception) {
            Log::notice('abm.program.save_failed', ['exception_type' => get_class($exception)]);
            return back()->withInput()->withErrors(['save' => 'Draf tidak disimpan. Cuba lagi atau muat semula halaman.']);
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409) { throw $exception; }
            return back()->withInput()->withErrors(['save' => 'Bidaan atau Program/ Aktiviti telah berubah. Salin perubahan anda sebelum memuat semula halaman.']);
        }
        return redirect()->route('programs.edit', [$bid, $saved->id])->with('status', 'Draf Program/ Aktiviti disimpan.');
    }
}
