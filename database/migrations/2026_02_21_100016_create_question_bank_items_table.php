<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank_items', function (Blueprint $table) {
            $table->id();

            // null = global system question (published by Survvy super admin)
            // set = institution's own custom bank question
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();

            $table->enum('type', ['rating', 'likert', 'multiple_choice', 'open_text']);
            $table->enum('category', ['instructor_evaluation', 'classroom_experience', 'general']);
            $table->text('question_text');
            $table->jsonb('config')->nullable()->comment('Options, scale, labels — same structure as survey_questions.config');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank_items');
    }
};
