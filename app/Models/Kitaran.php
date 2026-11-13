<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tahun'])]
class Kitaran extends Model
{
    public function fasa(): HasMany { return $this->hasMany(Fasa::class); }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
        ];
    }
}
