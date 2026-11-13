<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['jenis_skop', 'ptj_id', 'aktiviti_kewangan_id'])]
class SkopFasa extends Model
{
    protected $table = 'skop_fasa';
    public $timestamps = false;

    public function fasa(): BelongsTo { return $this->belongsTo(Fasa::class); }
    public function ptj(): BelongsTo { return $this->belongsTo(Ptj::class); }
    public function aktivitiKewangan(): BelongsTo { return $this->belongsTo(AktivitiKewangan::class); }
}
