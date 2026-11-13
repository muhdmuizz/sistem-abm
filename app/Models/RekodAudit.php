<?php

namespace App\Models;

use App\Enums\Peranan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class RekodAudit extends Model
{
    protected $table = 'rekod_audit';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'peranan' => Peranan::class, 'skop_pelaku' => 'array',
            'sebelum' => 'array', 'selepas' => 'array', 'berlaku_pada' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit history is append-only.'));
        static::deleting(fn () => throw new LogicException('Audit history is append-only.'));
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelaku_id');
    }
}
