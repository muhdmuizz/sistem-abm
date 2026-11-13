<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kod_ptj', 'nama', 'kategori', 'daerah'])]
class Ptj extends Model
{
    public function tempatBertugas(): HasMany
    {
        return $this->hasMany(TempatBertugas::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kod_ptj' => 'string',
        ];
    }
}
