<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('id');
            $table->index('team_id', 'roles_team_id_index');
        });

        // Drop old unique constraint and recreate with team_id included
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->unique(['team_id', 'name', 'guard_name']);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'name', 'guard_name']);
            $table->unique(['name', 'guard_name']);
            $table->dropIndex('roles_team_id_index');
            $table->dropColumn('team_id');
        });
    }
};
