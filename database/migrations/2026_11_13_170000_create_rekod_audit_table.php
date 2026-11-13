<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekod_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelaku_id')->constrained('users')->restrictOnDelete();
            $table->string('peranan');
            $table->jsonb('skop_pelaku');
            $table->string('tindakan');
            $table->string('jenis_entiti');
            $table->string('id_entiti');
            $table->jsonb('sebelum');
            $table->jsonb('selepas');
            $table->text('alasan')->nullable();
            $table->foreignId('ptj_id')->nullable()->constrained('ptjs')->restrictOnDelete();
            $table->foreignId('aktiviti_kewangan_id')->nullable()->constrained('aktiviti_kewangans')->restrictOnDelete();
            $table->unsignedBigInteger('tempat_bertugas_id')->nullable();
            $table->foreign(['tempat_bertugas_id', 'ptj_id'], 'audit_workplace_parent_foreign')
                ->references(['id', 'ptj_id'])->on('tempat_bertugas')->restrictOnDelete();
            $table->foreignId('kitaran_id')->nullable()->constrained('kitarans')->restrictOnDelete();
            $table->string('fasa_rujukan')->nullable();
            $table->unsignedInteger('versi_penghantaran')->nullable();
            $table->timestampTz('berlaku_pada', 6);
            $table->index(['jenis_entiti', 'id_entiti', 'id'], 'audit_entity_index');
            $table->index(['pelaku_id', 'berlaku_pada'], 'audit_actor_time_index');
        });
        DB::statement("ALTER TABLE rekod_audit ADD CONSTRAINT audit_role_check CHECK (peranan IN ('superadmin', 'ketua_bidaan', 'penyelia_bidaan', 'penyedia_bidaan', 'penyedia_anggaran', 'pemantau_bidaan'))");
        DB::statement('ALTER TABLE rekod_audit ADD CONSTRAINT audit_identity_check CHECK (length(trim(tindakan)) > 0 AND length(trim(jenis_entiti)) > 0 AND length(trim(id_entiti)) > 0)');
        DB::statement('ALTER TABLE rekod_audit ADD CONSTRAINT audit_context_check CHECK ((tempat_bertugas_id IS NULL OR ptj_id IS NOT NULL) AND (versi_penghantaran IS NULL OR versi_penghantaran > 0))');
        DB::statement(<<<'SQL'
CREATE OR REPLACE FUNCTION abm_reject_audit_mutation() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    RAISE EXCEPTION 'Audit history is append-only' USING ERRCODE = '55000';
END;
$$
SQL);
        DB::statement('CREATE TRIGGER audit_no_update_delete BEFORE UPDATE OR DELETE ON rekod_audit FOR EACH ROW EXECUTE FUNCTION abm_reject_audit_mutation()');
        DB::statement('CREATE TRIGGER audit_no_truncate BEFORE TRUNCATE ON rekod_audit FOR EACH STATEMENT EXECUTE FUNCTION abm_reject_audit_mutation()');
        DB::statement('ALTER TABLE rekod_audit ENABLE ALWAYS TRIGGER audit_no_update_delete');
        DB::statement('ALTER TABLE rekod_audit ENABLE ALWAYS TRIGGER audit_no_truncate');
    }

    public function down(): void
    {
        Schema::dropIfExists('rekod_audit');
        DB::statement('DROP FUNCTION abm_reject_audit_mutation()');
    }
};
