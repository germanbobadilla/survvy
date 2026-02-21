<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('lti_resource_link_id')->nullable()->comment('Moodle resource link ID set on first LTI launch');
            $table->string('unique_token')->unique()->comment('Used in LTI deep link URL to identify this assignment');
            $table->boolean('active')->default(false)->comment('Manager can toggle this on/off independently of survey status');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();

            $table->unique(['survey_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_assignments');
    }
};
