<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kod_objek', 'butiran_perbelanjaan', 'dikecualikan'])]
class RujukanOs extends Model
{
    protected $table = 'rujukan_os';

    protected function casts(): array
    {
        return ['kod_objek' => 'string', 'dikecualikan' => 'boolean'];
    }

    public function pemetaan(): HasMany
    {
        return $this->hasMany(PemetaanOs::class);
    }

    /** Reference eligibility only; grants no PTJ/user permission or wizard readiness. */
    public function scopeUntukPelaksanaan(Builder $query): Builder
    {
        return $query->where('dikecualikan', false);
    }
}
