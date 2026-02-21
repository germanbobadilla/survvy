<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'timezone',
        'settings',
        'active',
    ];

    protected $casts = [
        'settings' => 'array',
        'active'   => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function campuses()
    {
        return $this->hasMany(Campus::class);
    }

    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function ltiPlatforms()
    {
        return $this->hasMany(LtiPlatform::class);
    }
}
