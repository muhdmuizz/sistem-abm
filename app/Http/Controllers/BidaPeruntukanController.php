<?php

namespace App\Http\Controllers;

use App\Enums\Peranan;
use App\Models\Bidaan;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Models\TempatBertugas;
use App\Services\PengaktifanKewangan;
use App\Services\RuangBidaPeruntukan;
use App\Services\SkopPeranan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
        $cards = $this->osCards($request, $record, $workspace);
        $open = $workspace->open($record->kitaran_id, $record->ptj_id, $record->aktiviti_kewangan_id);
        [$payrollCards, $otherCards] = $cards->partition(fn ($card) =>
            app()->environment('local') && in_array($card['os']->kod_objek, PengaktifanKewangan::PAYROLL_OS, true));

        return view('abm.bids.show', [
            'bid' => $record, 'cards' => $cards, 'open' => $open,
            'payrollCards' => $payrollCards, 'otherCards' => $otherCards,
            'payrollActivation' => $this->payrollActivation($payrollCards),
        ]);
    }

    private function osCards(Request $request, Bidaan $bid, RuangBidaPeruntukan $workspace): \Illuminate\Support\Collection
    {
        $catalogue = $workspace->catalogue($request->user(), $bid);
        $estimates = $bid->anggaran()->whereIn('rujukan_os_id', $catalogue->pluck('id'))->get()->keyBy('rujukan_os_id');

        return $catalogue->map(fn ($os) => ['os' => $os, 'estimate' => $estimates->get($os->id),
            'activation' => $workspace->activation($request->user(), $bid->kitaran_id, $bid->ptj_id,
                $bid->aktiviti_kewangan_id, $bid, $os, $estimates->get($os->id))]);
    }

    private function payrollActivation(\Illuminate\Support\Collection $cards): array
    {
        $mapped = $cards->pluck('os.kod_objek')->values()->all() === PengaktifanKewangan::PAYROLL_OS;
        $pending = $cards->reject(fn ($card) => $card['estimate']?->diaktifkan);
        $blocked = $pending->first(fn ($card) => ! $card['activation']['allowed']);

        return [
            'active' => $mapped && $pending->isEmpty(),
            'allowed' => $mapped && $pending->isNotEmpty() && $blocked === null,
            'selector' => $pending->first()['os']->id ?? null,
            'explanation' => $mapped ? ($blocked['activation']['explanation'] ?? null) : 'Pemetaan atau akses bagi ketiga-tiga OS belum tersedia.',
            'needs_reason' => $cards->contains(fn ($card) => $card['activation']['needs_reason']),
            'central' => $cards->every(fn ($card) => $card['activation']['central']),
            'open' => $cards->every(fn ($card) => $card['activation']['open']),
        ];
    }

    private function payrollPreviewContext(Request $request, int $bid, RuangBidaPeruntukan $workspace): array
    {
        abort_unless(app()->environment('local'), 404);
        $record = $this->bidContext($request, $bid, $workspace);

        $cards = $this->osCards($request, $record, $workspace)
            ->filter(fn ($card) => in_array($card['os']->kod_objek, PengaktifanKewangan::PAYROLL_OS, true));
        abort_unless($cards->isNotEmpty(), 404);
        abort_unless($this->payrollActivation($cards)['active'], 409, 'Aktifkan OS Gaji & Emolumen terlebih dahulu.');

        $workplaces = [];
        if ($record->ptj->kategori === 'PPD') {
            $actor = $request->user();
            $roles = app(SkopPeranan::class);
            $role = Peranan::PenyediaAnggaran;
            $hasPpdScope = $roles->meliputi($actor->id, $role, $record->ptj_id, $record->aktiviti_kewangan_id);
            $costCentres = TempatBertugas::where('ptj_id', $record->ptj_id)->where('jenis', '!=', 'sektor');

            if ($hasPpdScope) {
                if ($record->aktivitiKewangan->singkatan === 'PPN'
                    || $record->aktivitiKewangan->nama === 'Pengurusan Pentadbiran Negeri') {
                    $workplaces['office'] = 'Pejabat PPD';
                }
            } else {
                abort_unless($actor->tempat_bertugas_id && $roles->meliputi(
                    $actor->id, $role, $record->ptj_id, $record->aktiviti_kewangan_id, $actor->tempat_bertugas_id,
                ), 403);
                $costCentres->whereKey($actor->tempat_bertugas_id);
            }

            foreach ($costCentres->orderBy('nama')->orderBy('id')->get(['id', 'nama']) as $centre) {
                $workplaces[$centre->id] = $centre->nama;
            }
            abort_if($workplaces === [], 403);
        }

        return [$record, $workplaces];
    }

    public function gajiPreview(Request $request, int $bid, RuangBidaPeruntukan $workspace, \App\Services\RujukanJawatan $jawatan)
    {
        [$record, $workplaces] = $this->payrollPreviewContext($request, $bid, $workspace);
        $saved = DB::table('draf_pratonton_gaji')->where('bidaan_id', $bid)
            ->where('user_id', $request->user()->id)->first();
        $draft = $saved ? json_decode(Crypt::decryptString($saved->payload), true, flags: JSON_THROW_ON_ERROR) : null;
        if ($draft !== null) {
            $draft['workplaces'] = array_intersect_key($draft['workplaces'], $workplaces ?: ['default' => true]);
            $draft['included'] = array_values(array_intersect($draft['included'], array_keys($workplaces)));
        }

        $allowanceRows = [];
        $source = file(base_path('docs/references/REF-08-kadar-elaun-imbuhan-tetap.md'), FILE_IGNORE_NEW_LINES);
        foreach ($source as $line) {
            if (! str_starts_with($line, '| ')) {
                continue;
            }
            $cells = array_map('trim', explode('|', trim($line, '| ')));
            if (in_array($cells[0], ['Imbuhan Tetap Keraian', 'Imbuhan Tetap Khidmat Awam', 'Imbuhan Tetap Perumahan', 'Bayaran Imbuhan Perumahan Wilayah', 'Bayaran Imbuhan Sara Hidup', 'Bayaran Imbuhan Wilayah'], true)) {
                $allowanceRows[] = $cells;
            }
        }

        return view('abm.estimates.gaji-preview', [
            'allowanceRows' => $allowanceRows,
            'draft' => $draft,
            'draftVersion' => $saved->revision ?? 0,
            'bid' => $record,
            'workplaces' => $workplaces,
            'year' => $record->kitaran->tahun,
            'salaryRates' => $jawatan->salaryRates(),
        ]);
    }

    public function saveGajiPreview(Request $request, int $bid, RuangBidaPeruntukan $workspace)
    {
        [, $workplaces] = $this->payrollPreviewContext($request, $bid, $workspace);
        $data = $request->validate(['revision' => 'required|integer|min:0', 'snapshot' => 'required|json|max:2000000']);
        $snapshot = json_decode($data['snapshot'], true, flags: JSON_THROW_ON_ERROR);
        abort_unless(is_array($snapshot), 422);
        Validator::make($snapshot, [
            'included' => 'present|array|list',
            'included.*' => 'required|string|distinct',
            'workplaces' => 'present|array',
            'workplaces.*' => 'required|array:jik,wizard,ketua',
            'workplaces.*.ketua' => 'present|nullable|string|max:10',
            'workplaces.*.jik' => 'present|array|list',
            'workplaces.*.jik.*' => 'required|array|list|size:3',
            'workplaces.*.jik.*.*' => 'present|nullable|string|max:200',
            'workplaces.*.wizard' => 'present|array|list',
            'workplaces.*.wizard.*' => 'required|array:position,vacant,fields,other',
            'workplaces.*.wizard.*.position' => 'required|string|max:200',
            'workplaces.*.wizard.*.vacant' => 'required|boolean',
            'workplaces.*.wizard.*.fields' => 'required|array|list|min:7|max:8',
            'workplaces.*.wizard.*.fields.*' => 'present|nullable|string|max:200',
            'workplaces.*.wizard.*.other' => 'present|array|list',
            'workplaces.*.wizard.*.other.*' => 'required|array|list|size:2',
            'workplaces.*.wizard.*.other.*.*' => 'present|nullable|string|max:120',
        ])->validate();

        $allowed = array_keys($workplaces ?: ['default' => true]);
        abort_if(array_diff(array_keys($snapshot['workplaces']), $allowed)
            || array_diff($snapshot['included'], array_keys($workplaces)), 403);

        $revision = DB::transaction(function () use ($bid, $request, $data) {
            Bidaan::whereKey($bid)->lockForUpdate()->firstOrFail();
            $key = ['bidaan_id' => $bid, 'user_id' => $request->user()->id];
            $table = DB::table('draf_pratonton_gaji');
            $saved = (clone $table)->where($key)->first();
            $revision = (int) ($saved->revision ?? 0);
            $expected = (int) $data['revision'];
            if ($saved && $revision === $expected + 1 && Crypt::decryptString($saved->payload) === $data['snapshot']) {
                return $revision;
            }
            abort_unless($revision === $expected, 409);
            $table->updateOrInsert($key, [
                'revision' => $revision + 1,
                'payload' => Crypt::encryptString($data['snapshot']),
            ]);

            return $revision + 1;
        });

        return response()->json(['revision' => $revision]);
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
            'bid' => $bid, 'os' => null, 'activation' => $activation,
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
        $payrollGroup = app()->environment('local') && in_array($reference->kod_objek, PengaktifanKewangan::PAYROLL_OS, true);
        if ($payrollGroup) {
            $cards = $this->osCards($request, $record, $workspace)
                ->filter(fn ($card) => in_array($card['os']->kod_objek, PengaktifanKewangan::PAYROLL_OS, true));
            $activation = $this->payrollActivation($cards);
        }
        abort_unless($activation['allowed'], 403);
        return view('abm.bids.activate', ['payrollGroup' => $payrollGroup, 'cycle' => $record->kitaran, 'ptj' => $record->ptj,
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
        $reference = $workspace->catalogue($request->user(), $record)->firstWhere('id', $os);
        abort_unless($reference, 404);
        $payrollGroup = app()->environment('local') && in_array($reference->kod_objek, PengaktifanKewangan::PAYROLL_OS, true);
        $data = $request->validate(['semakan' => 'required|integer|min:0', 'reason' => 'nullable|string|max:1000']);
        try {
            if ($payrollGroup) {
                $service->aktifkanGaji($bid, (int) $data['semakan'], $data['reason'] ?? null);
            } else {
                $service->aktifkanOs($bid, $os, (int) $data['semakan'], $data['reason'] ?? null);
            }
        } catch (QueryException $exception) {
            Log::notice('abm.os.activation_failed', ['exception_type' => get_class($exception)]);
            return $this->failure($request, 'OS tidak diaktifkan. Muat semula dan cuba lagi.');
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409) { throw $exception; }
            return $this->failure($request, 'Bidaan atau OS telah berubah. Muat semula dan semak status semasa.');
        }
        return redirect()->route('bids.show', $bid)->with('status', $payrollGroup ? 'OS Gaji & Emolumen diaktifkan.' : 'OS diaktifkan.');
    }
}
