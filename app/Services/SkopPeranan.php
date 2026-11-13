<?php

namespace App\Services;

use App\Enums\Peranan;
use App\Models\AktivitiKewangan;
use App\Models\PenugasanPeranan;
use App\Models\Ptj;
use App\Models\TempatBertugas;
use App\Models\User;

/** Role-scope eligibility only; callers must also enforce permissions and workflow state. */
class SkopPeranan
{
    public function meliputi(int $userId, Peranan $role, int $ptjId, int $activityId, ?int $workplaceId = null): bool
    {
        $user = User::with('ptj')->find($userId);
        if ($user === null || ! $user->is_active || $user->ptj === null ||
            ! Ptj::whereKey($ptjId)->exists() || ! AktivitiKewangan::whereKey($activityId)->exists()) {
            return false;
        }
        if ($workplaceId !== null && ! TempatBertugas::whereKey($workplaceId)->where('ptj_id', $ptjId)->exists()) {
            return false;
        }
        // Reload actual actor and assignments each time; never impersonate another user.
        if (PenugasanPeranan::where('user_id', $userId)->where('peranan', Peranan::Superadmin->value)->exists()) {
            return true;
        }
        if ($role === Peranan::KetuaBidaan && $user->ptj->kategori !== 'JPNT') {
            return false;
        }
        foreach (PenugasanPeranan::where('user_id', $userId)->where('peranan', $role->value)->get() as $assignment) {
            $matches = match ($role) {
                Peranan::Superadmin => false,
                Peranan::KetuaBidaan, Peranan::PenyeliaBidaan => $assignment->aktiviti_kewangan_id === $activityId,
                Peranan::PenyediaBidaan => $assignment->ptj_id === $ptjId && $assignment->aktiviti_kewangan_id === $activityId,
                Peranan::PenyediaAnggaran => $assignment->ptj_id === $ptjId && $assignment->aktiviti_kewangan_id === $activityId &&
                    ($assignment->tempat_bertugas_id === null ||
                        ($workplaceId !== null && $assignment->tempat_bertugas_id === $workplaceId && $user->tempat_bertugas_id === $workplaceId)),
                Peranan::PemantauBidaan => ($assignment->ptj_id === null || $assignment->ptj_id === $ptjId) &&
                    ($assignment->aktiviti_kewangan_id === null || $assignment->aktiviti_kewangan_id === $activityId),
            };
            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
