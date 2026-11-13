<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'singkatan'])]
class AktivitiKewangan extends Model
{
    public function kodAktiviti(): HasMany
    {
        return $this->hasMany(KodAktiviti::class);
    }
}
