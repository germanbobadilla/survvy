<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class SurveyResponse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'survey_assignment_id', 'respondent_id',
        'context', 'status', 'submitted_at',
    ];

    protected $casts = [
        'context'      => 'array',
        'submitted_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment()
    {
        return $this->belongsTo(SurveyAssignment::class, 'survey_assignment_id');
    }

    public function respondent()
    {
        return $this->belongsTo(Respondent::class);
    }

    public function answers()
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
