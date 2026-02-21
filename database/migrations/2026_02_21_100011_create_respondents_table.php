<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respondents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('lti_sub')->comment('LTI subject identifier — unique per user per platform');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->jsonb('lti_roles')->nullable()->comment('Roles as received from LTI claims');
            $table->timestamps();

            $table->unique(['tenant_id', 'lti_sub']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respondents');
    }
};
