<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lti_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->comment('Platform issuer URL e.g. https://moodle.institution.edu');
            $table->string('client_id')->unique()->comment('Unique client ID issued to this tenant tool registration');
            $table->string('oidc_auth_url')->comment('Platform OIDC authorization endpoint');
            $table->string('jwks_url')->comment('Platform JWK Set URL for JWT validation');
            $table->string('token_url')->comment('Platform access token endpoint');
            $table->text('private_key')->comment('Tool private key for signing JWTs');
            $table->text('public_key')->comment('Tool public key exposed via JWKS endpoint');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'issuer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lti_platforms');
    }
};
