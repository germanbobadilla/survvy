<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class LtiPlatform extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'issuer', 'client_id',
        'oidc_auth_url', 'jwks_url', 'token_url',
        'private_key', 'public_key', 'active',
    ];

    protected $hidden = ['private_key'];

    protected $casts = ['active' => 'boolean'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
