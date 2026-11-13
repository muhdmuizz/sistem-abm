<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['aktiviti_kewangan_id', 'kod_aktiviti_id', 'rujukan_os_id', 'singkatan_sumber', 'butiran_asal', 'status_sumber'])]
class PemetaanOs extends Model
{
    protected $table = 'pemetaan_os';

    public function aktivitiKewangan(): BelongsTo
    {
        return $this->belongsTo(AktivitiKewangan::class);
    }

    public function kodAktiviti(): BelongsTo
    {
        return $this->belongsTo(KodAktiviti::class);
    }

    public function rujukanOs(): BelongsTo
    {
        return $this->belongsTo(RujukanOs::class);
    }

    /** Reference eligibility only; does not grant user/PTJ permissions. */
    public function scopeUntukPelaksanaan(Builder $query): Builder
    {
        return $query->whereHas('rujukanOs', fn (Builder $os) => $os->untukPelaksanaan());
    }
}
