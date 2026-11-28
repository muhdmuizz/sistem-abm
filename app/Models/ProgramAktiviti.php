<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Non-financial programme metadata; no Anggaran/output/completeness store. */
class ProgramAktiviti extends Model
{
    protected $table = 'program_aktiviti';
    protected $guarded = ['*'];
    protected function casts(): array
    {
        return ['os_terpilih' => 'array', 'perkhidmatan_terpilih' => 'array', 'semakan' => 'integer', 'tahun_kitaran' => 'integer',
            'tarikh_mula' => 'immutable_date', 'tarikh_tamat' => 'immutable_date'];
    }
    public function bidaan(): BelongsTo { return $this->belongsTo(Bidaan::class); }
    public function tempatBertugas(): BelongsTo { return $this->belongsTo(TempatBertugas::class); }
    public function teras(): BelongsTo { return $this->belongsTo(TerasStrategik::class, 'teras_strategik_id'); }
    public function strategi(): BelongsTo { return $this->belongsTo(StrategiRpm::class, 'strategi_id'); }
    public function prakarsa(): BelongsTo { return $this->belongsTo(PrakarsaRpm::class, 'prakarsa_id'); }
    public function komponen(): HasMany { return $this->hasMany(KomponenProgram::class); }
    public function bilangan(): HasMany { return $this->hasMany(BilanganProgram::class); }

    /** PostgreSQL SUM(bigint) is exact numeric, including totals above PHP_INT_MAX. */
    public function jumlah(string $group): string
    {
        return (string) $this->bilangan()->where('kumpulan', $group)->sum('bilangan');
    }

    /** Metadata readiness only; never a programme/OS financial completion verdict. */
    public function kekuranganMaklumat(): array
    {
        $missing = [];
        foreach (['nama','jenis','kategori','peringkat','kaedah','tarikh_mula','tarikh_tamat'] as $field) {
            if ($this->{$field} === null || (is_string($this->{$field}) && trim($this->{$field}) === '')) { $missing[] = $field; }
        }
        foreach (['jemputan','peserta'] as $group) {
            if (! $this->bilangan()->where('kumpulan', $group)->exists()) { $missing[] = $group; }
        }
        if ($this->kaedah === 'Pakej' && ! $this->komponen()->exists()) { $missing[] = 'komponen'; }
        return $missing;
    }
}
