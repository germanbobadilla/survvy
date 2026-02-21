<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Respondent extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'lti_sub', 'name', 'email', 'lti_roles'];

    protected $casts = ['lti_roles' => 'array'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'enrollments');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
