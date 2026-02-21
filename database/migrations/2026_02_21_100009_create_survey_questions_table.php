<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('section')->nullable()->comment('Section/group label to visually group questions');
            $table->unsignedSmallInteger('order')->default(0);
            $table->enum('type', ['rating', 'likert', 'multiple_choice', 'open_text']);
            $table->text('question_text');
            $table->jsonb('config')->nullable()->comment('Options, scale min/max, labels, etc.');
            $table->boolean('required')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
