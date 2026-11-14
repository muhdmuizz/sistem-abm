<?php

namespace App\Models;

use App\Enums\StatusBidaan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Storage foundation only; future audited services own activation and financial writes. */
class Bidaan extends Model
{
    protected $table = 'bidaan';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['diaktifkan' => 'boolean', 'semakan' => 'integer', 'status' => StatusBidaan::class];
    }

    public function ptj(): BelongsTo
    {
        return $this->belongsTo(Ptj::class);
    }

    public function aktivitiKewangan(): BelongsTo
    {
        return $this->belongsTo(AktivitiKewangan::class);
    }

    public function kitaran(): BelongsTo
    {
        return $this->belongsTo(Kitaran::class);
    }

    public function programAktiviti(): HasMany
    {
        return $this->hasMany(ProgramAktiviti::class);
    }

    public function anggaran(): HasMany
    {
        return $this->hasMany(Anggaran::class);
    }
}
