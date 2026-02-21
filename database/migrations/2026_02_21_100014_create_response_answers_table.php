<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();
            // answer JSONB examples:
            // rating:          {"value": 4}
            // likert:          {"value": "agree"}
            // multiple_choice: {"selected": ["A", "C"]}
            // open_text:       {"text": "Great instructor, very clear explanations."}
            $table->jsonb('answer');
            $table->timestamps();

            $table->unique(['survey_response_id', 'survey_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_answers');
    }
};
