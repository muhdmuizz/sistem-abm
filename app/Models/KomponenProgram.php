<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenProgram extends Model
{
    protected $table = 'komponen_program';
    protected $guarded = ['*'];
    public $timestamps = false;
    public function programAktiviti(): BelongsTo { return $this->belongsTo(ProgramAktiviti::class); }
}
