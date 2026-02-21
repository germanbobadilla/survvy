<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Group extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'course_id', 'instructor_id',
        'name', 'code', 'lms_group_id', 'active',
    ];

    protected $casts = ['active' => 'boolean'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function respondents()
    {
        return $this->belongsToMany(Respondent::class, 'enrollments');
    }

    public function surveyAssignments()
    {
        return $this->hasMany(SurveyAssignment::class);
    }
}
