<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teras_strategik', function (Blueprint $table) {
            $table->id(); $table->integer('nombor')->unique(); $table->text('tajuk'); $table->timestamps();
        });
        Schema::create('strategi_rpm', function (Blueprint $table) {
            $table->id(); $table->foreignId('teras_strategik_id')->constrained('teras_strategik')->restrictOnDelete();
            $table->integer('nombor'); $table->text('tajuk'); $table->timestamps();
            $table->unique(['teras_strategik_id', 'nombor']);
            $table->unique(['id', 'teras_strategik_id'], 'strategi_parent_identity_unique');
        });
        Schema::create('prakarsa_rpm', function (Blueprint $table) {
            $table->id(); $table->foreignId('strategi_id')->constrained('strategi_rpm')->restrictOnDelete();
            $table->integer('nombor'); $table->text('tajuk'); $table->timestamps();
            $table->unique(['strategi_id', 'nombor']);
            $table->unique(['id', 'strategi_id'], 'prakarsa_parent_identity_unique');
        });
        foreach (['teras_strategik', 'strategi_rpm', 'prakarsa_rpm'] as $table) {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$table}_number_check CHECK (nombor > 0 AND length(trim(tajuk)) > 0)");
        }
        Schema::table('bidaan', fn (Blueprint $table) =>
            $table->unique(['id', 'ptj_id', 'kitaran_id'], 'bidaan_program_context_unique'));
        Schema::create('program_aktiviti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bidaan_id'); $table->unsignedBigInteger('ptj_id');
            $table->unsignedBigInteger('kitaran_id'); $table->integer('tahun_kitaran');
            $table->unsignedBigInteger('tempat_bertugas_id');
            $table->unsignedBigInteger('teras_strategik_id'); $table->unsignedBigInteger('strategi_id'); $table->unsignedBigInteger('prakarsa_id');
            foreach (['nama','jenis','kategori','peringkat','kaedah'] as $field) { $table->text($field)->nullable(); }
            $table->date('tarikh_mula')->nullable(); $table->date('tarikh_tamat')->nullable();
            $table->bigInteger('semakan')->default(0); $table->timestamps();
            $table->foreign(['bidaan_id','ptj_id','kitaran_id'], 'program_bid_context_foreign')
                ->references(['id','ptj_id','kitaran_id'])->on('bidaan')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['kitaran_id','tahun_kitaran'], 'program_cycle_year_foreign')
                ->references(['id','tahun'])->on('kitarans')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['tempat_bertugas_id','ptj_id'], 'program_workplace_foreign')
                ->references(['id','ptj_id'])->on('tempat_bertugas')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreign('teras_strategik_id')->references('id')->on('teras_strategik')->restrictOnDelete();
            $table->foreign(['strategi_id','teras_strategik_id'], 'program_strategy_parent_foreign')
                ->references(['id','teras_strategik_id'])->on('strategi_rpm')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['prakarsa_id','strategi_id'], 'program_initiative_parent_foreign')
                ->references(['id','strategi_id'])->on('prakarsa_rpm')->restrictOnDelete()->restrictOnUpdate();
            $table->index(['bidaan_id','tempat_bertugas_id','id'], 'program_scope_page_index');
        });
        DB::statement("ALTER TABLE program_aktiviti ADD CONSTRAINT program_values_check CHECK (
            semakan >= 0 AND tahun_kitaran BETWEEN 1 AND 9999
            AND (jenis IS NULL OR jenis IN ('Umum','Sukan','Kokurikulum'))
            AND (kategori IS NULL OR kategori IN ('Kursus/ Bengkel','Taklimat','Mesyuarat','Perjumpaan','Pemantauan'))
            AND (peringkat IS NULL OR peringkat IN ('Kebangsaan','Negeri','Daerah'))
            AND (kaedah IS NULL OR kaedah IN ('Pakej','Bukan Pakej'))
            AND (tarikh_mula IS NULL OR EXTRACT(YEAR FROM tarikh_mula) = tahun_kitaran)
            AND (tarikh_tamat IS NULL OR EXTRACT(YEAR FROM tarikh_tamat) = tahun_kitaran)
            AND (tarikh_mula IS NULL OR tarikh_tamat IS NULL OR tarikh_tamat >= tarikh_mula))");
        Schema::create('komponen_program', function (Blueprint $table) {
            $table->id(); $table->foreignId('program_aktiviti_id')->constrained('program_aktiviti')->restrictOnDelete();
            $table->text('komponen'); $table->unique(['program_aktiviti_id','komponen']);
        });
        DB::statement("ALTER TABLE komponen_program ADD CONSTRAINT program_component_check CHECK (komponen IN ('Penginapan','Makan Minum','Sewaan Dewan','Sewaan Peralatan'))");
        Schema::create('bilangan_program', function (Blueprint $table) {
            $table->id(); $table->foreignId('program_aktiviti_id')->constrained('program_aktiviti')->restrictOnDelete();
            $table->string('kumpulan'); $table->integer('turutan');
            $table->string('kategori'); $table->bigInteger('klasifikasi')->nullable();
            $table->text('catatan')->nullable(); $table->bigInteger('bilangan');
            $table->unique(['program_aktiviti_id','kumpulan','turutan'], 'program_headcount_position_unique');
        });
        DB::statement("ALTER TABLE bilangan_program ADD CONSTRAINT program_headcount_check CHECK (
            kumpulan IN ('jemputan','peserta') AND turutan > 0 AND bilangan > 0
            AND (kumpulan = 'peserta' OR kategori IN ('Pegawai','AKP'))
            AND (
                (kategori IN ('Pegawai','Guru') AND klasifikasi IS NOT NULL AND klasifikasi >= 9 AND catatan IS NULL)
                OR (kategori = 'AKP' AND klasifikasi IS NOT NULL AND klasifikasi BETWEEN 1 AND 8 AND catatan IS NULL)
                OR (kategori IN ('Murid Rendah','Murid Menengah') AND klasifikasi IS NOT NULL AND klasifikasi BETWEEN 1 AND 6 AND catatan IS NULL)
                OR (kategori = 'Lain-lain' AND klasifikasi IS NULL AND catatan IS NOT NULL AND length(trim(catatan)) > 0)
            ))");
    }

    public function down(): void
    {
        Schema::dropIfExists('bilangan_program'); Schema::dropIfExists('komponen_program');
        Schema::dropIfExists('program_aktiviti');
        Schema::table('bidaan', fn (Blueprint $table) => $table->dropUnique('bidaan_program_context_unique'));
        Schema::dropIfExists('prakarsa_rpm'); Schema::dropIfExists('strategi_rpm'); Schema::dropIfExists('teras_strategik');
    }
};
