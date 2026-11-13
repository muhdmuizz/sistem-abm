<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fasa extends Model
{
    protected $table = 'fasa';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['tahun_kitaran' => 'integer', 'nombor_pindaan' => 'integer',
            'tarikh_mula' => 'immutable_date', 'tarikh_tamat' => 'immutable_date',
            'dibuka' => 'boolean', 'pernah_dibuka' => 'boolean', 'semakan' => 'integer'];
    }

    public function kitaran(): BelongsTo { return $this->belongsTo(Kitaran::class); }
    public function skop(): HasMany { return $this->hasMany(SkopFasa::class); }

    public function label(): string
    {
        return $this->jenis === 'bidaan_awal' ? 'Bidaan Awal' : 'Pindaan '.$this->nombor_pindaan;
    }

    public function dalamTempoh(?CarbonImmutable $when = null): bool
    {
        $date = ($when ?? CarbonImmutable::now('Asia/Kuala_Lumpur'))->setTimezone('Asia/Kuala_Lumpur')->toDateString();
        return $date >= $this->tarikh_mula->toDateString() && $date <= $this->tarikh_tamat->toDateString();
    }
}
