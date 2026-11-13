<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kod_aktivitis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aktiviti_kewangan_id')
                ->constrained('aktiviti_kewangans')
                ->restrictOnDelete();
            $table->string('kod_aktiviti', 6)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kod_aktivitis');
    }
};
