<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class SurveyQuestion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'survey_id', 'tenant_id', 'question_bank_item_id',
        'section', 'order', 'type', 'question_text', 'config', 'required',
    ];

    protected $casts = [
        'config'   => 'array',
        'required' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function questionBankItem()
    {
        return $this->belongsTo(QuestionBankItem::class);
    }

    public function answers()
    {
        return $this->hasMany(ResponseAnswer::class);
    }
}
