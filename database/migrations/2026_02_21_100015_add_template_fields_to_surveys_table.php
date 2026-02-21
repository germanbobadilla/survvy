<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // tenant_id is nullable to allow system-level (Survvy global) templates
            $table->foreignId('tenant_id')->nullable()->change();

            $table->boolean('is_template')->default(false)->after('status')
                ->comment('True for both system templates and institution templates');

            $table->foreignId('source_template_id')->nullable()->after('is_template')
                ->constrained('surveys')->nullOnDelete()
                ->comment('Set when a survey is cloned from a template');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['source_template_id']);
            $table->dropColumn(['is_template', 'source_template_id']);
        });
    }
};
