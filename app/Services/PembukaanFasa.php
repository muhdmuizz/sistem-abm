<?php

namespace App\Services;

use App\Models\AktivitiKewangan;
use App\Models\Fasa;
use App\Models\Ptj;
use Carbon\CarbonImmutable;

/** Opening-window eligibility only: callers still enforce role/central/financial-state rules. */
class PembukaanFasa
{
    public function dibenarkan(int $cycleId, int $ptjId, int $activityId, ?CarbonImmutable $when = null): bool
    {
        if (! Ptj::whereKey($ptjId)->exists() || ! AktivitiKewangan::whereKey($activityId)->exists()) { return false; }
        $date = ($when ?? CarbonImmutable::now('Asia/Kuala_Lumpur'))->setTimezone('Asia/Kuala_Lumpur')->toDateString();
        return Fasa::where('kitaran_id', $cycleId)->where('dibuka', true)
            ->where('tarikh_mula', '<=', $date)->where('tarikh_tamat', '>=', $date)
            ->whereHas('skop', fn ($q) => $q
                ->where(fn ($p) => $p->whereNull('ptj_id')->orWhere('ptj_id', $ptjId))
                ->where(fn ($a) => $a->whereNull('aktiviti_kewangan_id')->orWhere('aktiviti_kewangan_id', $activityId)))
            ->exists();
    }
}
