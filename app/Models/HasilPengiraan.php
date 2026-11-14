<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Future approved calculators supply output values; this model performs no calculation. */
class HasilPengiraan extends Model
{
    protected $table = 'hasil_pengiraan';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        // Do not coerce exact database NUMERIC into binary float or fixed-scale decimal.
        return ['amaun' => 'string', 'data_output' => 'object', 'turutan' => 'integer', 'semakan_sumber' => 'integer'];
    }

    public function butiranAnggaran(): BelongsTo
    {
        return $this->belongsTo(ButiranAnggaran::class);
    }
}
