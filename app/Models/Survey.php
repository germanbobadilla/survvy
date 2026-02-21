<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Survey extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'created_by', 'title', 'description',
        'type', 'status', 'is_template', 'source_template_id',
        'starts_at', 'ends_at', 'settings',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
        'settings'    => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sourceTemplate()
    {
        return $this->belongsTo(Survey::class, 'source_template_id');
    }

    public function clones()
    {
        return $this->hasMany(Survey::class, 'source_template_id');
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    public function assignments()
    {
        return $this->hasMany(SurveyAssignment::class);
    }

    public function isGlobalTemplate(): bool
    {
        return $this->is_template && is_null($this->tenant_id);
    }
}
