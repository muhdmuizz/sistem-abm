<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptjs', function (Blueprint $table) {
            $table->id();
            $table->string('kod_ptj')->unique();
            $table->string('nama');
            $table->string('kategori');
            $table->string('daerah')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptjs');
    }
};
