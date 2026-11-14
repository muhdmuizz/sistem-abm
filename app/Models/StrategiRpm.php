<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Approved RPM reference; local numbering is scoped by its real parent. */
class StrategiRpm extends Model
{
    protected $table = 'strategi_rpm';
    protected $guarded = ['*'];
    protected function casts(): array { return ['nombor' => 'integer']; }
    public function teras(): BelongsTo { return $this->belongsTo(TerasStrategik::class, 'teras_strategik_id'); }
    public function prakarsa(): HasMany { return $this->hasMany(PrakarsaRpm::class, 'strategi_id'); }
}
