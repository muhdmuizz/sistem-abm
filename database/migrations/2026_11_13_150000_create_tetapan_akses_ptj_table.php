<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tetapan_akses_ptj', function (Blueprint $table) {
            $table->id();
            $table->string('kategori_ptj')->nullable();
            $table->foreignId('ptj_id')->nullable()->constrained('ptjs')->restrictOnDelete();
            $table->foreignId('aktiviti_kewangan_id')->constrained('aktiviti_kewangans')->restrictOnDelete();
            $table->foreignId('rujukan_os_id')->nullable()->constrained('rujukan_os')->restrictOnDelete();
            $table->boolean('dibenarkan');
            $table->timestamps();
            $table->index(['ptj_id', 'aktiviti_kewangan_id'], 'tetapan_akses_ptj_activity_index');
            $table->index(['kategori_ptj', 'aktiviti_kewangan_id'], 'tetapan_akses_category_activity_index');
        });
        DB::statement("ALTER TABLE tetapan_akses_ptj ADD CONSTRAINT tetapan_akses_category_check CHECK (kategori_ptj IS NULL OR kategori_ptj IN ('JPNT', 'PPD', 'SMK PTJ'))");
        DB::statement('ALTER TABLE tetapan_akses_ptj ADD CONSTRAINT tetapan_akses_target_check CHECK ((kategori_ptj IS NOT NULL AND ptj_id IS NULL) OR (kategori_ptj IS NULL AND ptj_id IS NOT NULL))');
        DB::statement('CREATE UNIQUE INDEX tetapan_akses_identity_unique ON tetapan_akses_ptj (kategori_ptj, ptj_id, aktiviti_kewangan_id, rujukan_os_id) NULLS NOT DISTINCT');
    }

    public function down(): void
    {
        Schema::dropIfExists('tetapan_akses_ptj');
    }
};
