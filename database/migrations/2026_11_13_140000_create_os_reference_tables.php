<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kod_aktivitis', function (Blueprint $table) {
            $table->unique(['id', 'aktiviti_kewangan_id'], 'kod_aktiviti_parent_unique');
        });
        Schema::create('rujukan_os', function (Blueprint $table) {
            $table->id();
            $table->string('kod_objek', 5);
            $table->string('butiran_perbelanjaan');
            $table->boolean('dikecualikan')->default(false);
            $table->timestamps();
            $table->unique(['kod_objek', 'butiran_perbelanjaan'], 'rujukan_os_identity_unique');
        });
        DB::statement("ALTER TABLE rujukan_os ADD CONSTRAINT rujukan_os_code_check CHECK (kod_objek ~ '^[0-9]{5}$')");
        DB::statement('ALTER TABLE rujukan_os ADD CONSTRAINT rujukan_os_description_check CHECK (length(trim(butiran_perbelanjaan)) > 0)');

        Schema::create('pemetaan_os', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aktiviti_kewangan_id')->constrained('aktiviti_kewangans')->restrictOnDelete();
            $table->unsignedBigInteger('kod_aktiviti_id')->nullable();
            $table->foreignId('rujukan_os_id')->constrained('rujukan_os')->restrictOnDelete();
            $table->string('singkatan_sumber');
            $table->string('butiran_asal');
            $table->string('status_sumber');
            $table->timestamps();
            $table->foreign(['kod_aktiviti_id', 'aktiviti_kewangan_id'], 'pemetaan_os_code_parent_foreign')
                ->references(['id', 'aktiviti_kewangan_id'])->on('kod_aktivitis')->restrictOnDelete();
            $table->index(['kod_aktiviti_id', 'aktiviti_kewangan_id'], 'pemetaan_os_code_parent_index');
        });
        // PostgreSQL: blank-code mappings must also remain unique across reruns.
        DB::statement('CREATE UNIQUE INDEX pemetaan_os_identity_unique ON pemetaan_os (aktiviti_kewangan_id, kod_aktiviti_id, rujukan_os_id) NULLS NOT DISTINCT');
    }

    public function down(): void
    {
        Schema::dropIfExists('pemetaan_os');
        Schema::dropIfExists('rujukan_os');
        Schema::table('kod_aktivitis', function (Blueprint $table) {
            $table->dropUnique('kod_aktiviti_parent_unique');
        });
    }
};
