<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cadangan_item_anggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggaran')->restrictOnDelete();
            $table->foreignId('pemohon_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('ptj_id')->constrained('ptjs')->restrictOnDelete();
            $table->foreignId('penyemak_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('kod_os', 5);
            $table->string('kategori');
            $table->string('nama');
            $table->string('nama_normal');
            $table->string('unit', 40);
            $table->decimal('kadar_cadangan', 18, 2);
            $table->decimal('kadar_diluluskan', 18, 2)->nullable();
            $table->text('justifikasi');
            $table->text('catatan_keputusan')->nullable();
            $table->enum('status', ['menunggu', 'diluluskan', 'ditolak'])->default('menunggu');
            $table->timestampTz('diputuskan_pada')->nullable();
            $table->timestampsTz();
        });

        DB::statement("CREATE UNIQUE INDEX cadangan_item_aktif_unik ON cadangan_item_anggaran (kod_os, kategori, nama_normal) WHERE status <> 'ditolak'");
    }

    public function down(): void
    {
        Schema::dropIfExists('cadangan_item_anggaran');
    }
};
