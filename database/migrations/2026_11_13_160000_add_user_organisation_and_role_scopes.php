<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempat_bertugas', function (Blueprint $table) {
            $table->unique(['id', 'ptj_id'], 'tempat_bertugas_parent_unique');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(false);
            $table->foreignId('ptj_id')->nullable()->constrained('ptjs')->restrictOnDelete();
            $table->unsignedBigInteger('tempat_bertugas_id')->nullable();
            $table->foreign(['tempat_bertugas_id', 'ptj_id'], 'users_workplace_parent_foreign')
                ->references(['id', 'ptj_id'])->on('tempat_bertugas')->restrictOnDelete();
        });
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_workplace_context_check CHECK (tempat_bertugas_id IS NULL OR ptj_id IS NOT NULL)');

        Schema::create('penugasan_peranan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('peranan');
            $table->foreignId('ptj_id')->nullable()->constrained('ptjs')->restrictOnDelete();
            $table->foreignId('aktiviti_kewangan_id')->nullable()->constrained('aktiviti_kewangans')->restrictOnDelete();
            $table->unsignedBigInteger('tempat_bertugas_id')->nullable();
            $table->timestamps();
            $table->foreign(['tempat_bertugas_id', 'ptj_id'], 'penugasan_workplace_parent_foreign')
                ->references(['id', 'ptj_id'])->on('tempat_bertugas')->restrictOnDelete();
            $table->index(['user_id', 'peranan'], 'penugasan_user_role_index');
        });
        DB::statement(<<<'SQL'
ALTER TABLE penugasan_peranan ADD CONSTRAINT penugasan_role_scope_check CHECK (
    (peranan = 'superadmin' AND ptj_id IS NULL AND aktiviti_kewangan_id IS NULL AND tempat_bertugas_id IS NULL)
 OR (peranan IN ('ketua_bidaan', 'penyelia_bidaan') AND ptj_id IS NULL AND aktiviti_kewangan_id IS NOT NULL AND tempat_bertugas_id IS NULL)
 OR (peranan = 'penyedia_bidaan' AND ptj_id IS NOT NULL AND aktiviti_kewangan_id IS NOT NULL AND tempat_bertugas_id IS NULL)
 OR (peranan = 'penyedia_anggaran' AND ptj_id IS NOT NULL AND aktiviti_kewangan_id IS NOT NULL)
 OR (peranan = 'pemantau_bidaan' AND tempat_bertugas_id IS NULL AND (ptj_id IS NULL OR aktiviti_kewangan_id IS NULL))
)
SQL);
        DB::statement('CREATE UNIQUE INDEX penugasan_role_identity_unique ON penugasan_peranan (user_id, peranan, ptj_id, aktiviti_kewangan_id, tempat_bertugas_id) NULLS NOT DISTINCT');
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_peranan');
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_workplace_context_check');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_workplace_parent_foreign');
            $table->dropForeign(['ptj_id']);
            $table->dropColumn(['is_active', 'ptj_id', 'tempat_bertugas_id']);
        });
        Schema::table('tempat_bertugas', function (Blueprint $table) {
            $table->dropUnique('tempat_bertugas_parent_unique');
        });
    }
};
