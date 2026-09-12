<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngagementQuestionBank extends EngagementModel
{
    use SoftDeletes;

    protected $table = 'engagement_question_bank';

    protected $fillable = [
        'tenant_id',
        'code',
        'question',
        'description',
        'question_type',
        'category',
        'dimension',
        'default_scale',
        'options',
        'is_system',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default_scale' => 'array',
            'options' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function surveyQuestions(): HasMany
    {
        return $this->hasMany(EngagementSurveyQuestion::class, 'bank_question_id');
    }
}
