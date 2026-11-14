<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidaan', function (Blueprint $table) {
            $table->string('status')->default('dalam_penyediaan');
        });
        DB::statement("ALTER TABLE bidaan ADD CONSTRAINT bidaan_workflow_status_check CHECK (status IN ('dalam_penyediaan', 'dalam_semakan', 'dikembalikan', 'muktamad'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bidaan DROP CONSTRAINT bidaan_workflow_status_check');
        Schema::table('bidaan', fn (Blueprint $table) => $table->dropColumn('status'));
    }
};
