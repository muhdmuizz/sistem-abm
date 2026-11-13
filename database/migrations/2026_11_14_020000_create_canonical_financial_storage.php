<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempat_bertugas', fn (Blueprint $table) =>
            $table->unique(['id', 'ptj_id'], 'workplace_financial_parent_unique'));

        Schema::create('bidaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ptj_id')->constrained('ptjs')->restrictOnDelete();
            $table->foreignId('aktiviti_kewangan_id')->constrained('aktiviti_kewangans')->restrictOnDelete();
            $table->foreignId('kitaran_id')->constrained('kitarans')->restrictOnDelete();
            $table->boolean('diaktifkan')->default(false);
            $table->bigInteger('semakan')->default(0);
            $table->timestamps();
            $table->unique(['ptj_id', 'aktiviti_kewangan_id', 'kitaran_id'], 'bidaan_identity_unique');
            $table->unique(['id', 'ptj_id'], 'bidaan_ptj_parent_unique');
            $table->index(['kitaran_id', 'aktiviti_kewangan_id', 'ptj_id'], 'bidaan_cycle_activity_index');
        });
        DB::statement('ALTER TABLE bidaan ADD CONSTRAINT bidaan_revision_check CHECK (semakan >= 0)');

        Schema::create('anggaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bidaan_id');
            $table->unsignedBigInteger('ptj_id');
            $table->foreignId('rujukan_os_id')->constrained('rujukan_os')->restrictOnDelete();
            $table->boolean('diaktifkan')->default(false);
            $table->bigInteger('semakan')->default(0);
            $table->timestamps();
            $table->foreign(['bidaan_id', 'ptj_id'], 'anggaran_bidaan_ptj_foreign')
                ->references(['id', 'ptj_id'])->on('bidaan')->restrictOnDelete()->restrictOnUpdate();
            $table->unique(['bidaan_id', 'rujukan_os_id'], 'anggaran_os_identity_unique');
            $table->unique(['id', 'ptj_id'], 'anggaran_ptj_parent_unique');
            $table->index(['bidaan_id', 'ptj_id'], 'anggaran_parent_index');
        });
        DB::statement('ALTER TABLE anggaran ADD CONSTRAINT anggaran_revision_check CHECK (semakan >= 0)');

        Schema::create('butiran_anggaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anggaran_id');
            $table->unsignedBigInteger('ptj_id');
            $table->unsignedBigInteger('tempat_bertugas_id');
            // An object envelope, not an approved OS schema or completeness verdict.
            $table->jsonb('data_sumber')->default(DB::raw("'{}'::jsonb"));
            $table->bigInteger('semakan')->default(0);
            $table->timestamps();
            $table->foreign(['anggaran_id', 'ptj_id'], 'butiran_anggaran_parent_foreign')
                ->references(['id', 'ptj_id'])->on('anggaran')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tempat_bertugas_id', 'ptj_id'], 'butiran_workplace_ptj_foreign')
                ->references(['id', 'ptj_id'])->on('tempat_bertugas')->restrictOnDelete()->restrictOnUpdate();
            $table->unique(['id', 'semakan'], 'butiran_revision_parent_unique');
            $table->index(['anggaran_id', 'id'], 'butiran_anggaran_page_index');
            $table->index(['tempat_bertugas_id', 'ptj_id'], 'butiran_workplace_index');
        });
        DB::statement("ALTER TABLE butiran_anggaran ADD CONSTRAINT butiran_source_object_check CHECK (jsonb_typeof(data_sumber) = 'object')");
        DB::statement('ALTER TABLE butiran_anggaran ADD CONSTRAINT butiran_revision_check CHECK (semakan >= 0)');

        Schema::create('hasil_pengiraan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('butiran_anggaran_id');
            $table->bigInteger('semakan_sumber');
            $table->bigInteger('turutan');
            $table->jsonb('data_output')->default(DB::raw("'{}'::jsonb"));
            $table->timestamps();
            $table->foreign(['butiran_anggaran_id', 'semakan_sumber'], 'hasil_source_revision_foreign')
                ->references(['id', 'semakan'])->on('butiran_anggaran')->restrictOnDelete()->restrictOnUpdate();
            $table->unique(['butiran_anggaran_id', 'turutan'], 'hasil_source_order_unique');
            $table->index(['butiran_anggaran_id', 'semakan_sumber'], 'hasil_source_revision_index');
        });
        // Unconstrained NUMERIC preserves exact values without inventing OS rounding/scale.
        DB::statement('ALTER TABLE hasil_pengiraan ADD COLUMN amaun numeric NOT NULL');
        DB::statement("ALTER TABLE hasil_pengiraan ADD CONSTRAINT hasil_amount_finite_check CHECK (amaun NOT IN ('NaN'::numeric, 'Infinity'::numeric, '-Infinity'::numeric))");
        DB::statement("ALTER TABLE hasil_pengiraan ADD CONSTRAINT hasil_output_object_check CHECK (jsonb_typeof(data_output) = 'object')");
        DB::statement('ALTER TABLE hasil_pengiraan ADD CONSTRAINT hasil_order_revision_check CHECK (turutan > 0 AND semakan_sumber >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_pengiraan');
        Schema::dropIfExists('butiran_anggaran');
        Schema::dropIfExists('anggaran');
        Schema::dropIfExists('bidaan');
        Schema::table('tempat_bertugas', fn (Blueprint $table) =>
            $table->dropUnique('workplace_financial_parent_unique'));
    }
};
