<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitarans', fn (Blueprint $table) => $table->unique(['id', 'tahun'], 'kitaran_id_year_unique'));
        Schema::create('fasa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kitaran_id');
            $table->integer('tahun_kitaran');
            $table->string('jenis');
            $table->integer('nombor_pindaan')->nullable();
            $table->date('tarikh_mula');
            $table->date('tarikh_tamat');
            $table->string('jenis_skop');
            $table->boolean('dibuka')->default(false);
            $table->boolean('pernah_dibuka')->default(false);
            $table->integer('semakan')->default(0);
            $table->timestamps();
            $table->foreign(['kitaran_id', 'tahun_kitaran'], 'fasa_kitaran_year_foreign')
                ->references(['id', 'tahun'])->on('kitarans')->restrictOnDelete()->restrictOnUpdate();
            $table->unique(['kitaran_id', 'nombor_pindaan'], 'fasa_pindaan_number_unique');
            $table->unique(['id', 'jenis_skop'], 'fasa_scope_parent_unique');
            $table->index(['kitaran_id', 'dibuka', 'tarikh_mula', 'tarikh_tamat'], 'fasa_window_index');
        });
        DB::statement("ALTER TABLE fasa ADD CONSTRAINT fasa_kind_number_check CHECK ((jenis = 'bidaan_awal' AND nombor_pindaan IS NULL) OR (jenis = 'pindaan' AND nombor_pindaan > 0 AND nombor_pindaan IS NOT NULL))");
        DB::statement("ALTER TABLE fasa ADD CONSTRAINT fasa_scope_kind_check CHECK (jenis_skop IN ('global', 'ptj', 'aktiviti', 'gabungan'))");
        DB::statement('ALTER TABLE fasa ADD CONSTRAINT fasa_dates_check CHECK (tahun_kitaran BETWEEN 1 AND 9999 AND tarikh_tamat >= tarikh_mula AND EXTRACT(YEAR FROM tarikh_mula) = tahun_kitaran AND EXTRACT(YEAR FROM tarikh_tamat) = tahun_kitaran)');
        DB::statement('ALTER TABLE fasa ADD CONSTRAINT fasa_state_check CHECK (semakan >= 0 AND (NOT dibuka OR pernah_dibuka))');
        Schema::create('skop_fasa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fasa_id');
            $table->string('jenis_skop');
            $table->foreignId('ptj_id')->nullable()->constrained('ptjs')->restrictOnDelete();
            $table->foreignId('aktiviti_kewangan_id')->nullable()->constrained('aktiviti_kewangans')->restrictOnDelete();
            $table->foreign(['fasa_id', 'jenis_skop'], 'skop_fasa_parent_foreign')
                ->references(['id', 'jenis_skop'])->on('fasa')->restrictOnDelete()->restrictOnUpdate();
        });
        DB::statement("ALTER TABLE skop_fasa ADD CONSTRAINT skop_fasa_shape_check CHECK (
            (jenis_skop = 'global' AND ptj_id IS NULL AND aktiviti_kewangan_id IS NULL) OR
            (jenis_skop = 'ptj' AND ptj_id IS NOT NULL AND aktiviti_kewangan_id IS NULL) OR
            (jenis_skop = 'aktiviti' AND ptj_id IS NULL AND aktiviti_kewangan_id IS NOT NULL) OR
            (jenis_skop = 'gabungan' AND ptj_id IS NOT NULL AND aktiviti_kewangan_id IS NOT NULL))");
        DB::statement('CREATE UNIQUE INDEX skop_fasa_identity_unique ON skop_fasa (fasa_id, ptj_id, aktiviti_kewangan_id) NULLS NOT DISTINCT');
    }

    public function down(): void
    {
        Schema::dropIfExists('skop_fasa');
        Schema::dropIfExists('fasa');
        Schema::table('kitarans', fn (Blueprint $table) => $table->dropUnique('kitaran_id_year_unique'));
    }
};
