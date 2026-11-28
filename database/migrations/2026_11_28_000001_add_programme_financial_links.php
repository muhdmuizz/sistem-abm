<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('program_aktiviti', fn (Blueprint $table) => $table->jsonb('os_terpilih')->nullable());
        Schema::table('butiran_anggaran', function (Blueprint $table) {
            $table->foreignId('program_aktiviti_id')->nullable()->constrained('program_aktiviti')->restrictOnDelete();
            $table->index(['program_aktiviti_id', 'anggaran_id']);
        });
    }
    public function down(): void
    {
        Schema::table('butiran_anggaran', function (Blueprint $table) {
            $table->dropIndex(['program_aktiviti_id', 'anggaran_id']);
            $table->dropConstrainedForeignId('program_aktiviti_id');
        });
        Schema::table('program_aktiviti', fn (Blueprint $table) => $table->dropColumn('os_terpilih'));
    }
};
