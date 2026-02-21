<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBankItem extends Model
{
    protected $fillable = [
        'tenant_id', 'type', 'category', 'question_text', 'config', 'active',
    ];

    protected $casts = [
        'config' => 'array',
        'active' => 'boolean',
    ];

    // No BelongsToTenant trait — tenant_id is nullable (null = global system questions)
    // Scoping is handled manually in queries

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isGlobal(): bool
    {
        return is_null($this->tenant_id);
    }
}
