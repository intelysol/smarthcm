<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngagementSurveyQuestion extends EngagementModel
{
    protected $table = 'engagement_survey_questions';

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'section_id',
        'bank_question_id',
        'question',
        'description',
        'question_type',
        'category',
        'dimension',
        'scale_config',
        'options',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'scale_config' => 'array',
            'options' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EngagementSurvey::class, 'survey_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(EngagementSurveySection::class, 'section_id');
    }

    public function bankQuestion(): BelongsTo
    {
        return $this->belongsTo(EngagementQuestionBank::class, 'bank_question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EngagementResponseAnswer::class, 'question_id');
    }

    public function rulesAsSource(): HasMany
    {
        return $this->hasMany(EngagementQuestionRule::class, 'source_question_id');
    }

    public function rulesAsTarget(): HasMany
    {
        return $this->hasMany(EngagementQuestionRule::class, 'target_question_id');
    }
}
