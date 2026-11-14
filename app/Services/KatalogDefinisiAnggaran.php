<?php

namespace App\Services;

use App\Models\Bidaan;
use App\Models\PemetaanOs;
use App\Models\RujukanOs;

/** Definition selection is context-aware; it never grants financial access. */
class KatalogDefinisiAnggaran
{
    public function untuk(RujukanOs $os, Bidaan $bid): ?DefinisiAnggaran
    {
        $food = app(DefinisiAnggaran::class);
        if ($food->supports($os)) { return $food; }

        $sbk = app(DefinisiUtilitiSbk::class);
        if (! $sbk->supports($os)) { return null; }
        $code = match ($bid->aktivitiKewangan->nama) {
            'Pendidikan Rendah' => '030228',
            'Pendidikan Menengah' => '030310',
            default => null,
        };
        if ($code === null) { return null; }
        $mapped = PemetaanOs::where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id)
            ->where('rujukan_os_id', $os->id)
            ->whereHas('kodAktiviti', fn ($query) => $query->where('kod_aktiviti', $code)
                ->where('aktiviti_kewangan_id', $bid->aktiviti_kewangan_id))->exists();
        return $mapped ? $sbk : null;
    }
}
