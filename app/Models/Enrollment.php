<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Enrollment extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'group_id', 'respondent_id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function respondent()
    {
        return $this->belongsTo(Respondent::class);
    }
}
