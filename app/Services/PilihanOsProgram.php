<?php

namespace App\Services;

use App\Models\Bidaan;
use App\Models\RujukanOs;
use App\Models\User;
use Illuminate\Support\Collection;

class PilihanOsProgram
{
    public const LABELS = [
        '14000' => ['Bayaran Lebih Masa'],
        '21000' => DefinisiPerjalanan::LABELS,
        '24000' => ['Sewaan'],
        '25000' => ['Bahan-bahan Makanan dan Minuman'],
        '27000' => ['Bekalan Alat Pejabat'],
        '29000' => ['Perkhidmatan Iktisas dan Perkhidmatan Lain Yang Dibeli dan Hospitaliti', 'Pakej (Mesyuarat/ Bengkel/ Kursus)'],
    ];

    public static function supports(RujukanOs $os): bool
    {
        return ! $os->dikecualikan && in_array($os->butiran_perbelanjaan, self::LABELS[$os->kod_objek] ?? [], true);
    }

    public function choices(User $actor, Bidaan $bid): Collection
    {
        return app(RuangBidaPeruntukan::class)->catalogue($actor, $bid)
            ->filter(fn ($os) => self::supports($os) && app(KatalogDefinisiAnggaran::class)->untuk($os, $bid) !== null)->values();
    }
}
