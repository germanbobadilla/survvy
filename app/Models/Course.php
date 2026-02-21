<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Course extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'program_id', 'term_id',
        'name', 'code', 'lms_course_id', 'active',
    ];

    protected $casts = ['active' => 'boolean'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
