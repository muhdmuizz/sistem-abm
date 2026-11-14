<?php

namespace App\Services;

use App\Enums\Peranan;
use App\Models\Anggaran;
use App\Models\Bidaan;
use App\Models\ButiranAnggaran;
use App\Models\TempatBertugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/** Read-only metadata queries. Source fields require approved OS adapters. */
class RuangAnggaran
{
    public function __construct(private RuangBidaPeruntukan $workspace, private SkopPeranan $roles) {}

    public function sources(User $actor, Bidaan $bid, Anggaran $estimate): Builder
    {
        abort_unless($estimate->bidaan_id === $bid->id &&
            $this->workspace->canView($actor, $bid->ptj_id, $bid->aktiviti_kewangan_id), 403);
        abort_unless($this->workspace->catalogue($actor, $bid)->contains('id', $estimate->rujukan_os_id), 404);

        $wide = false;
        foreach ([Peranan::PenyediaBidaan, Peranan::PemantauBidaan, Peranan::PenyediaAnggaran] as $role) {
            if ($this->roles->meliputi($actor->id, $role, $bid->ptj_id, $bid->aktiviti_kewangan_id)) {
                $wide = true;
                break;
            }
        }
        $workplaces = TempatBertugas::where('ptj_id', $bid->ptj_id);
        if (! $wide) {
            $own = $actor->tempat_bertugas_id;
            abort_unless($own && $this->roles->meliputi($actor->id, Peranan::PenyediaAnggaran,
                $bid->ptj_id, $bid->aktiviti_kewangan_id, $own), 403);
            $workplaces->whereKey($own);
        }

        // Apply the scope before pagination/counting; never fetch opaque source JSON or outputs.
        return ButiranAnggaran::query()->where('anggaran_id', $estimate->id)->where('ptj_id', $bid->ptj_id)
            ->whereIn('tempat_bertugas_id', $workplaces->select('id'))
            ->select(['id', 'anggaran_id', 'tempat_bertugas_id', 'updated_at'])
            ->with('tempatBertugas:id,nama');
    }
}
