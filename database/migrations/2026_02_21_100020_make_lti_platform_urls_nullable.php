<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lti_platforms', function (Blueprint $table) {
            $table->string('oidc_auth_url')->nullable()->change();
            $table->string('jwks_url')->nullable()->change();
            $table->string('token_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lti_platforms', function (Blueprint $table) {
            $table->string('oidc_auth_url')->nullable(false)->change();
            $table->string('jwks_url')->nullable(false)->change();
            $table->string('token_url')->nullable(false)->change();
        });
    }
};
