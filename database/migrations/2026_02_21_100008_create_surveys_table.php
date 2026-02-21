<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['instructor_evaluation', 'classroom_experience']);
            $table->enum('status', ['draft', 'scheduled', 'active', 'closed'])->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->jsonb('settings')->nullable()->comment('Survey-level settings: allow_anonymous, show_progress, etc.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
