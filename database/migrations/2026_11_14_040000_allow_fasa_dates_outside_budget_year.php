<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE fasa DROP CONSTRAINT fasa_dates_check');
        DB::statement('ALTER TABLE fasa ADD CONSTRAINT fasa_dates_check CHECK (tahun_kitaran BETWEEN 1 AND 9999 AND tarikh_tamat >= tarikh_mula)');
    }

    public function down(): void
    {
        // Validation fails atomically if existing cross-year phases cannot satisfy the old rule.
        // Never rewrite or delete their dates to make rollback succeed.
        DB::statement('ALTER TABLE fasa DROP CONSTRAINT fasa_dates_check');
        DB::statement('ALTER TABLE fasa ADD CONSTRAINT fasa_dates_check CHECK (tahun_kitaran BETWEEN 1 AND 9999 AND tarikh_tamat >= tarikh_mula AND EXTRACT(YEAR FROM tarikh_mula) = tahun_kitaran AND EXTRACT(YEAR FROM tarikh_tamat) = tahun_kitaran)');
    }
};
