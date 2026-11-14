<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BilanganProgram extends Model
{
    protected $table = 'bilangan_program';
    protected $guarded = ['*'];
    public $timestamps = false;
    protected function casts(): array { return ['turutan' => 'integer', 'klasifikasi' => 'integer', 'bilangan' => 'integer']; }
    public function programAktiviti(): BelongsTo { return $this->belongsTo(ProgramAktiviti::class); }
}
