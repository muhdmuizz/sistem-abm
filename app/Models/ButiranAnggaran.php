<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Independent source identity. An incomplete saved object is not a validated OS row. */
class ButiranAnggaran extends Model
{
    protected $table = 'butiran_anggaran';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['data_sumber' => 'object', 'semakan' => 'integer'];
    }

    public function anggaran(): BelongsTo
    {
        return $this->belongsTo(Anggaran::class);
    }

    public function tempatBertugas(): BelongsTo
    {
        return $this->belongsTo(TempatBertugas::class);
    }

    public function hasilPengiraan(): HasMany
    {
        return $this->hasMany(HasilPengiraan::class);
    }
}
