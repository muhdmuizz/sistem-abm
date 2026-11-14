<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Approved RPM reference; local numbering is scoped by its real parent. */
class TerasStrategik extends Model
{
    protected $table = 'teras_strategik';
    protected $guarded = ['*'];
    protected function casts(): array { return ['nombor' => 'integer']; }
    public function strategi(): HasMany { return $this->hasMany(StrategiRpm::class, 'teras_strategik_id'); }
}
