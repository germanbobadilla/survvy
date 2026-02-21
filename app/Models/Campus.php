<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Campus extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}
