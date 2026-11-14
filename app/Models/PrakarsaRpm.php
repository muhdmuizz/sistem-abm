<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Approved RPM reference; local numbering is scoped by its real parent. */
class PrakarsaRpm extends Model
{
    protected $table = 'prakarsa_rpm';
    protected $guarded = ['*'];
    protected function casts(): array { return ['nombor' => 'integer']; }
    public function strategi(): BelongsTo { return $this->belongsTo(StrategiRpm::class, 'strategi_id'); }
}
