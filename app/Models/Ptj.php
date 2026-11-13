<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kod_ptj', 'nama', 'kategori', 'daerah'])]
class Ptj extends Model
{
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
