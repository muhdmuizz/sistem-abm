<?php

namespace App\Services;

use App\Enums\Peranan;
use App\Models\Bidaan;
use App\Models\ProgramAktiviti;
use App\Models\TempatBertugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** Scoped metadata query only; it confers no write or financial permission. */
class RuangProgramAktiviti
{
    public function __construct(private RuangBidaPeruntukan $workspace, private SkopPeranan $roles) {}

    public function query(User $actor, Bidaan $bid): Builder
    {
        abort_unless($this->workspace->canView($actor, $bid->ptj_id, $bid->aktiviti_kewangan_id), 403);
        $wide = false;
        foreach ([Peranan::PenyediaBidaan, Peranan::PemantauBidaan, Peranan::PenyediaAnggaran] as $role) {
            if ($this->roles->meliputi($actor->id, $role, $bid->ptj_id, $bid->aktiviti_kewangan_id)) { $wide = true; break; }
        }
        $workplaces = TempatBertugas::where('ptj_id', $bid->ptj_id);
        if (! $wide) {
            $own = $actor->tempat_bertugas_id;
            abort_unless($own && $this->roles->meliputi($actor->id, Peranan::PenyediaAnggaran,
                $bid->ptj_id, $bid->aktiviti_kewangan_id, $own), 403);
            $workplaces->whereKey($own);
        }
        return ProgramAktiviti::where('bidaan_id', $bid->id)->where('ptj_id', $bid->ptj_id)
            ->whereIn('tempat_bertugas_id', $workplaces->select('id'));
    }
}
