<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Term extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'starts_at', 'ends_at', 'active'];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'active'    => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
