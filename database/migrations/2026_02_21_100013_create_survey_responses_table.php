<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('survey_assignment_id')->constrained('survey_assignments')->cascadeOnDelete();
            $table->foreignId('respondent_id')->constrained('respondents')->cascadeOnDelete();

            // Denormalized context snapshot — captured at submission time for accurate reporting
            // even if academic structure is renamed or reorganized later
            $table->jsonb('context')->comment(
                'Snapshot: {campus_id, campus_name, program_id, program_name, term_id, term_name, ' .
                'course_id, course_name, course_code, group_id, group_name, ' .
                'instructor_id, instructor_name, survey_type}'
            );

            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // One submission per respondent per assignment — enforced at DB level
            $table->unique(['survey_assignment_id', 'respondent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
