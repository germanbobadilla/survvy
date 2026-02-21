<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';

        // Add team_id to model_has_permissions
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey) {
            $table->unsignedBigInteger($teamForeignKey)->nullable()->after('permission_id');
            $table->index($teamForeignKey, 'model_has_permissions_team_foreign_key_index');
        });

        // Add team_id to model_has_roles
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey) {
            $table->unsignedBigInteger($teamForeignKey)->nullable()->after('role_id');
            $table->index($teamForeignKey, 'model_has_roles_team_foreign_key_index');
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey) {
            $table->dropIndex('model_has_permissions_team_foreign_key_index');
            $table->dropColumn($teamForeignKey);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey) {
            $table->dropIndex('model_has_roles_team_foreign_key_index');
            $table->dropColumn($teamForeignKey);
        });
    }
};
