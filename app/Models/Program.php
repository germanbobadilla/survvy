<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Program extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'campus_id', 'name', 'code', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
