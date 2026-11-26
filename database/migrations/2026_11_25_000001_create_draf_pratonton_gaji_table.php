<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draf_pratonton_gaji', function (Blueprint $table) {
            $table->foreignId('bidaan_id')->constrained('bidaan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['bidaan_id', 'user_id']);
            $table->unsignedBigInteger('revision');
            $table->longText('payload');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draf_pratonton_gaji');
    }
};
