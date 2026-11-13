<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['aktiviti_kewangan_id', 'kod_aktiviti'])]
class KodAktiviti extends Model
{
    protected function casts(): array
    {
        return [
            'kod_aktiviti' => 'string',
        ];
    }

    public function pemetaanOs(): HasMany
    {
        return $this->hasMany(PemetaanOs::class);
    }

    public function aktivitiKewangan(): BelongsTo
    {
        return $this->belongsTo(AktivitiKewangan::class);
    }
}
