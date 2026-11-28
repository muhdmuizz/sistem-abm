<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('program_aktiviti', fn (Blueprint $table) => $table->jsonb('perkhidmatan_terpilih')->nullable());
    }
    public function down(): void
    {
        Schema::table('program_aktiviti', fn (Blueprint $table) => $table->dropColumn('perkhidmatan_terpilih'));
    }
};
