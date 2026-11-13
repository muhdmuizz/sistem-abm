<?php

namespace App\Models;

use App\Enums\Peranan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Internal storage; no assignment administration API is exposed by this model. */
#[Fillable(['user_id', 'peranan', 'ptj_id', 'aktiviti_kewangan_id', 'tempat_bertugas_id'])]
class PenugasanPeranan extends Model
{
    protected $table = 'penugasan_peranan';

    protected function casts(): array
    {
        return ['peranan' => Peranan::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ptj(): BelongsTo
    {
        return $this->belongsTo(Ptj::class);
    }

    public function aktivitiKewangan(): BelongsTo
    {
        return $this->belongsTo(AktivitiKewangan::class);
    }

    public function tempatBertugas(): BelongsTo
    {
        return $this->belongsTo(TempatBertugas::class);
    }
}
