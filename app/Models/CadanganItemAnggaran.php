<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CadanganItemAnggaran extends Model
{
    protected $table = 'cadangan_item_anggaran';

    public const STATUS = [
        'menunggu' => 'Menunggu Semakan',
        'diluluskan' => 'Diluluskan',
        'ditolak' => 'Ditolak',
    ];

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function ptj()
    {
        return $this->belongsTo(Ptj::class);
    }
}
