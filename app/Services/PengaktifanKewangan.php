<?php

namespace App\Services;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\AktivitiKewangan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\Kitaran;
use App\Models\Ptj;
use App\Models\RujukanOs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Initial preparation only. No source writes, completeness, calculations or submission transitions. */
class PengaktifanKewangan
{
    public function __construct(
        private JejakAudit $audit,
        private SkopPeranan $roles,
        private AksesPeruntukan $permissions,
        private PembukaanFasa $phases,
    ) {}

    private function actor(): User
    {
        $actor = User::with('ptj')->whereKey(Auth::id())->lockForUpdate()->first();
        $authenticated = session('abm_authenticated_at');
        $active = session('abm_last_activity');
        $now = now()->timestamp;
        abort_unless($actor && $actor->is_active && $actor->ptj !== null &&
            trim($actor->google_subject ?? '') !== '' &&
            session('abm_access_revision') === $actor->access_revision &&
            is_int($authenticated) && is_int($active) &&
            $authenticated <= $active && $active <= $now &&
            $now - $authenticated < 28800 && $now - $active < 1800, 403);
        $actor->setRelation('penugasanPeranan', $actor->penugasanPeranan()->lockForUpdate()->get());
        return $actor;
    }

    private function prepareLocks(int $cycleId): Kitaran
    {
        // Same key as central-permission administration: do not authorise against an interleaved edit.
        DB::select('select pg_advisory_xact_lock(41620101, 8010)');
        // Phase administration locks this parent too; creation is serialised without a per-phase identity.
        return Kitaran::whereKey($cycleId)->lockForUpdate()->firstOrFail();
    }

    private function authorise(User $actor, Peranan $role, int $cycle, int $ptj, int $activity, ?int $os, ?string $reason): array
    {
        $admin = $actor->penugasanPeranan->contains('peranan', Peranan::Superadmin);
        abort_unless($this->roles->meliputi($actor->id, $role, $ptj, $activity, $actor->tempat_bertugas_id), 403);
        $central = $os === null
            ? $this->permissions->bolehAksesAktiviti($ptj, $activity)
            : $this->permissions->bolehAksesOs($ptj, $activity, $os);
        $open = $this->phases->dibenarkan($cycle, $ptj, $activity);
        abort_unless($admin || ($central && $open), 403);
        $reason = trim($reason ?? '');
        $override = ['kebenaran_pusat' => ! $central, 'fasa' => ! $open];
        if (strlen($reason) > 4000 || mb_strlen($reason) > 1000 || ($admin && (! $central || ! $open) && $reason === '')) {
            throw ValidationException::withMessages(['reason' => 'Alasan sah diperlukan bagi pintasan Superadmin (maksimum 1000 aksara).']);
        }
        return ['override' => $override, 'reason' => $reason];
    }

    private function current(Bidaan $bid, int $revision): void
    {
        abort_unless($revision >= 0, 422);
        abort_unless($bid->semakan === $revision, 409, 'Bidaan telah berubah. Muat semula sebelum meneruskan.');
        abort_unless($bid->status === StatusBidaan::DalamPenyediaan, 409,
            'Pengaktifan awal hanya tersedia untuk Bidaan Dalam Penyediaan.');
    }

    private function state(Bidaan $bid, ?Anggaran $estimate = null): array
    {
        $state = ['bidaan_id' => $bid->id, 'ptj_id' => $bid->ptj_id,
            'aktiviti_kewangan_id' => $bid->aktiviti_kewangan_id, 'kitaran_id' => $bid->kitaran_id,
            'diaktifkan' => $bid->diaktifkan, 'status' => $bid->status->value, 'semakan' => $bid->semakan];
        if ($estimate !== null) {
            $state['anggaran'] = ['id' => $estimate->id, 'rujukan_os_id' => $estimate->rujukan_os_id,
                'diaktifkan' => $estimate->diaktifkan, 'semakan' => $estimate->semakan];
        }
        return $state;
    }

    public function aktifkanBidaan(int $cycleId, int $ptjId, int $activityId, int $expectedRevision, ?string $reason = null): Bidaan
    {
        return DB::transaction(function () use ($cycleId, $ptjId, $activityId, $expectedRevision, $reason) {
            $actor = $this->actor();
            $this->prepareLocks($cycleId);
            Ptj::findOrFail($ptjId);
            AktivitiKewangan::findOrFail($activityId);
            $authority = $this->authorise($actor, Peranan::PenyediaBidaan, $cycleId, $ptjId, $activityId, null, $reason);
            $bid = Bidaan::where('kitaran_id', $cycleId)->where('ptj_id', $ptjId)
                ->where('aktiviti_kewangan_id', $activityId)->lockForUpdate()->first();
            if ($bid) {
                $this->current($bid, $expectedRevision);
                if ($bid->diaktifkan) {
                    return $bid;
                }
                abort_if($bid->anggaran()->exists(), 409, 'Bidaan belum aktif mempunyai rekod OS; semakan pentadbiran diperlukan.');
            } else {
                abort_unless($expectedRevision === 0, 409, 'Konteks Bidaan telah berubah.');
            }
            $before = $bid ? $this->state($bid) : ['exists' => false];
            $this->audit->jalankan(Peranan::PenyediaBidaan, function () use (&$bid, $cycleId, $ptjId, $activityId, $authority, $before) {
                if ($bid === null) {
                    $bid = new Bidaan;
                    $bid->forceFill(['kitaran_id' => $cycleId, 'ptj_id' => $ptjId,
                        'aktiviti_kewangan_id' => $activityId, 'semakan' => 0, 'status' => StatusBidaan::DalamPenyediaan]);
                }
                $bid->diaktifkan = true;
                $bid->semakan++;
                $bid->save();
                return new PeristiwaAudit('bid.activated', Bidaan::class, (string) $bid->id,
                    $before, $this->state($bid) + ['pintasan' => $authority['override']],
                    $authority['reason'] ?: 'Aktifkan Bidaan.', ptjId: $ptjId, aktivitiId: $activityId, kitaranId: $cycleId);
            });
            return $bid->fresh();
        });
    }

    public function aktifkanOs(int $bidId, int $osId, int $expectedBidRevision, ?string $reason = null): Anggaran
    {
        return DB::transaction(function () use ($bidId, $osId, $expectedBidRevision, $reason) {
            $actor = $this->actor();
            $cycleId = Bidaan::whereKey($bidId)->value('kitaran_id');
            abort_if($cycleId === null, 404);
            $this->prepareLocks($cycleId);
            $bid = Bidaan::whereKey($bidId)->where('kitaran_id', $cycleId)->lockForUpdate()->firstOrFail();
            $authority = $this->authorise($actor, Peranan::PenyediaAnggaran, $cycleId, $bid->ptj_id, $bid->aktiviti_kewangan_id, $osId, $reason);
            $this->current($bid, $expectedBidRevision);
            abort_unless($bid->diaktifkan, 409, 'Aktifkan Bidaan terlebih dahulu.');
            // Superadmin overrides permission/time scope, never catalogue exclusion or activity parentage.
            RujukanOs::untukPelaksanaan()->whereKey($osId)
                ->whereHas('pemetaan', fn ($q) => $q->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id))
                ->lockForShare()->firstOrFail();
            $estimate = $bid->anggaran()->where('rujukan_os_id', $osId)->lockForUpdate()->first();
            if ($estimate?->diaktifkan) {
                return $estimate;
            }
            abort_if($estimate && $estimate->butiran()->exists(), 409,
                'Aktifkan semula OS berdata memerlukan pengesahan dan pengiraan terkini.');
            $before = $this->state($bid, $estimate);
            $this->audit->jalankan(Peranan::PenyediaAnggaran, function () use ($bid, $osId, &$estimate, $before, $authority) {
                if ($estimate === null) {
                    $estimate = new Anggaran;
                    $estimate->forceFill(['bidaan_id' => $bid->id, 'ptj_id' => $bid->ptj_id,
                        'rujukan_os_id' => $osId, 'semakan' => 0]);
                }
                $estimate->diaktifkan = true;
                $estimate->semakan++;
                $estimate->save();
                $bid->semakan++;
                $bid->save();
                return new PeristiwaAudit('estimate.activated', Anggaran::class, (string) $estimate->id,
                    $before, $this->state($bid, $estimate) + ['pintasan' => $authority['override']],
                    $authority['reason'] ?: 'Aktifkan OS.', ptjId: $bid->ptj_id,
                    aktivitiId: $bid->aktiviti_kewangan_id, kitaranId: $bid->kitaran_id);
            });
            return $estimate->fresh();
        });
    }
}
