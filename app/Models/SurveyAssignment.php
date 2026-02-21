<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Support\Str;

class SurveyAssignment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'survey_id', 'group_id',
        'lti_resource_link_id', 'unique_token',
        'active', 'activated_at', 'deactivated_at',
    ];

    protected $casts = [
        'active'          => 'boolean',
        'activated_at'    => 'datetime',
        'deactivated_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SurveyAssignment $assignment) {
            if (empty($assignment->unique_token)) {
                $assignment->unique_token = Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function activate(): void
    {
        $this->update(['active' => true, 'activated_at' => now()]);
    }

    public function deactivate(): void
    {
        $this->update(['active' => false, 'deactivated_at' => now()]);
    }
}
