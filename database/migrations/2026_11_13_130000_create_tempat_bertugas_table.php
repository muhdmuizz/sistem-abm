<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tempat_bertugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ptj_id')->constrained('ptjs')->restrictOnDelete();
            $table->string('nama');
            $table->string('jenis');
            $table->timestamps();
            $table->unique(['ptj_id', 'jenis', 'nama'], 'tempat_bertugas_identity_unique');
        });

        DB::statement("ALTER TABLE tempat_bertugas ADD CONSTRAINT tempat_bertugas_jenis_check CHECK (jenis IN ('sektor', 'sekolah_non_ptj', 'smk_ptj'))");
        DB::statement("ALTER TABLE tempat_bertugas ADD CONSTRAINT tempat_bertugas_nama_check CHECK (length(trim(nama)) > 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('tempat_bertugas');
    }
};
