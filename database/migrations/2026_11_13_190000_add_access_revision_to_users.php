<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->unsignedBigInteger('access_revision')->default(0));
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_access_revision_check CHECK (access_revision >= 0)');
        // Refuse ambiguous historical email identities rather than silently merging accounts.
        DB::statement('CREATE UNIQUE INDEX users_email_lower_unique ON users (lower(email))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX users_email_lower_unique');
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_access_revision_check');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('access_revision'));
    }
};
