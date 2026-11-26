<?php

namespace App\Services;

use App\Enums\Peranan;
use App\Enums\StatusBidaan;
use App\Models\Bidaan;
use App\Models\Kitaran;
use App\Models\RujukanOs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

/** Internal guard only: use inside an audited business transaction, not as a write API. */
class KawalanPenyediaanKewangan
{
    public function __construct(
        private SkopPeranan $roles,
        private AksesPeruntukan $permissions,
        private PembukaanFasa $phases,
    ) {}

    private function transaction(): void
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Financial guard requires an active transaction.');
        }
    }

    public function actor(): User
    {
        $this->transaction();
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

    public function prepareLocks(int $cycleId): Kitaran
    {
        $this->transaction();
        // Same key as central-permission administration: do not authorise against an interleaved edit.
        DB::select('select pg_advisory_xact_lock(41620101, 8010)');
        // Phase administration locks this parent too; creation is serialised without a per-phase identity.
        return Kitaran::whereKey($cycleId)->lockForUpdate()->firstOrFail();
    }

    public function authorise(User $actor, Peranan $role, int $cycle, int $ptj, int $activity, ?int $os, ?string $reason, ?int $workplaceId = null): array
    {
        $this->transaction();
        $admin = $actor->penugasanPeranan->contains('peranan', Peranan::Superadmin);
        $scopeWorkplace = $workplaceId ?? ($admin ? null : $actor->tempat_bertugas_id);
        abort_unless($this->roles->meliputi($actor->id, $role, $ptj, $activity, $scopeWorkplace), 403);
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

    public function current(Bidaan $bid, int $revision, array $allowed = [StatusBidaan::DalamPenyediaan]): void
    {
        $this->transaction();
        abort_unless($revision >= 0, 422);
        abort_unless($bid->semakan === $revision, 409, 'Bidaan telah berubah. Muat semula sebelum meneruskan.');
        abort_unless(in_array($bid->status, $allowed, true), 409,
            'Tindakan tidak tersedia untuk status Bidaan semasa.');
    }

    public function catalogue(Bidaan $bid, int $osId): RujukanOs
    {
        $this->transaction();
        return RujukanOs::untukPelaksanaan()->whereKey($osId)
            ->whereHas('pemetaan', fn ($q) => $q->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id))
            ->sharedLock()->firstOrFail();
    }
}
