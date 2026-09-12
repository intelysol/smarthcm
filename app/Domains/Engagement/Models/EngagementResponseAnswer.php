<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementResponseAnswer extends EngagementModel
{
    protected $table = 'engagement_response_answers';

    protected $fillable = [
        'tenant_id',
        'response_id',
        'question_id',
        'option_id',
        'numeric_value',
        'text_value',
        'nps_category',
        'favorability_status',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:4',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(EngagementResponse::class, 'response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EngagementSurveyQuestion::class, 'question_id');
    }
}
