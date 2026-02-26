<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LtiPlatform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Multitenancy\Models\Tenant;

class LtiSettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $tenant = Tenant::current();
        $platform = LtiPlatform::first();

        return Inertia::render('Settings/Lti', [
            'platform'   => $platform ? $platform->only(
                'id', 'name', 'client_id', 'issuer',
                'oidc_auth_url', 'jwks_url', 'token_url', 'public_key', 'active'
            ) : null,
            'toolUrls' => [
                'launch'  => url('/lti/launch'),
                'login'   => url('/lti/login'),
                'jwks'    => url('/lti/jwks'),
                'deepLink' => url('/lti/deep-link'),
            ],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
        ]);

        // Generate RSA-2048 key pair
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($resource, $privateKey);
        $details   = openssl_pkey_get_details($resource);
        $publicKey = $details['key'];

        $clientId = (string) Str::uuid();

        LtiPlatform::updateOrCreate(
            ['tenant_id' => Tenant::current()->id],
            [
                'name'       => $request->name,
                'issuer'     => $request->issuer,
                'client_id'  => $clientId,
                'private_key' => $privateKey,
                'public_key'  => $publicKey,
                'active'     => true,
            ]
        );

        return back()->with('success', 'LTI credentials generated successfully.');
    }
}
