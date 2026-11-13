<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One transactional OS per Bidaan; activation is separate from future completeness. */
class Anggaran extends Model
{
    protected $table = 'anggaran';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['diaktifkan' => 'boolean', 'semakan' => 'integer'];
    }

    public function bidaan(): BelongsTo
    {
        return $this->belongsTo(Bidaan::class);
    }

    public function rujukanOs(): BelongsTo
    {
        return $this->belongsTo(RujukanOs::class);
    }

    public function butiran(): HasMany
    {
        return $this->hasMany(ButiranAnggaran::class);
    }
}
