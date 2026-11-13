<?php

namespace App\Services;

use App\Models\PemetaanOs;
use App\Models\Ptj;
use App\Models\TetapanAksesPtj;

/** Resolves PTJ configuration only; never substitutes for user/role authorisation. */
class AksesPeruntukan
{
    /** Null means unconfigured, distinct from an explicit false setting. */
    public function tetapanAktiviti(int $ptjId, int $aktivitiId): ?bool
    {
        return $this->resolve($ptjId, $aktivitiId, null);
    }

    /** Returns the stored effective OS setting even if the parent activity is disabled. */
    public function tetapanOs(int $ptjId, int $aktivitiId, int $osId): ?bool
    {
        return $this->resolve($ptjId, $aktivitiId, $osId);
    }

    public function bolehAksesAktiviti(int $ptjId, int $aktivitiId): bool
    {
        return $this->tetapanAktiviti($ptjId, $aktivitiId) === true;
    }

    public function bolehAksesOs(int $ptjId, int $aktivitiId, int $osId): bool
    {
        if (! $this->bolehAksesAktiviti($ptjId, $aktivitiId) ||
            $this->tetapanOs($ptjId, $aktivitiId, $osId) !== true) {
            return false;
        }

        return PemetaanOs::untukPelaksanaan()
            ->where('aktiviti_kewangan_id', $aktivitiId)
            ->where('rujukan_os_id', $osId)
            ->exists();
    }

    private function resolve(int $ptjId, int $aktivitiId, ?int $osId): ?bool
    {
        // Reload category and settings on each evaluation; do not cache old grants.
        $ptj = Ptj::find($ptjId);
        if ($ptj === null) {
            return null;
        }
        $settings = TetapanAksesPtj::where('aktiviti_kewangan_id', $aktivitiId)
            ->where('rujukan_os_id', $osId);
        $override = (clone $settings)->where('ptj_id', $ptjId)->first();
        if ($override !== null) {
            return $override->dibenarkan;
        }

        return $settings->where('kategori_ptj', $ptj->kategori)->first()?->dibenarkan;
    }
}
