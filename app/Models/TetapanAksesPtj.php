<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Configuration storage only; administrative mutations require authorisation and audit. */
#[Fillable(['kategori_ptj', 'ptj_id', 'aktiviti_kewangan_id', 'rujukan_os_id', 'dibenarkan'])]
class TetapanAksesPtj extends Model
{
    protected $table = 'tetapan_akses_ptj';

    protected function casts(): array
    {
        return ['dibenarkan' => 'boolean'];
    }

    public function ptj(): BelongsTo
    {
        return $this->belongsTo(Ptj::class);
    }

    public function aktivitiKewangan(): BelongsTo
    {
        return $this->belongsTo(AktivitiKewangan::class);
    }

    public function rujukanOs(): BelongsTo
    {
        return $this->belongsTo(RujukanOs::class);
    }
}
