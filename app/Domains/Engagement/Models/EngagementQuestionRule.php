<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementQuestionRule extends EngagementModel
{
    protected $table = 'engagement_question_rules';

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'source_question_id',
        'target_question_id',
        'operator',
        'value',
        'action',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EngagementSurvey::class, 'survey_id');
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(EngagementSurveyQuestion::class, 'source_question_id');
    }

    public function targetQuestion(): BelongsTo
    {
        return $this->belongsTo(EngagementSurveyQuestion::class, 'target_question_id');
    }
}
