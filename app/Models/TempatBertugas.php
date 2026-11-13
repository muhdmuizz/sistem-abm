<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ptj_id', 'nama', 'jenis'])]
class TempatBertugas extends Model
{
    protected $table = 'tempat_bertugas';

    public function ptj(): BelongsTo
    {
        return $this->belongsTo(Ptj::class);
    }

    /** Organisational references only; this scope grants no user permission. */
    public function scopeSektor(Builder $query): Builder
    {
        return $query->where('jenis', 'sektor');
    }
}
