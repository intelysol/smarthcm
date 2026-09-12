<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementTextAnalysis extends EngagementModel
{
    public $timestamps = false;

    protected $table = 'engagement_text_analysis';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'question_id',
        'topic',
        'sentiment',
        'confidence',
        'sample_count',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:4',
            'sample_count' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EngagementCampaign::class, 'campaign_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EngagementSurveyQuestion::class, 'question_id');
    }
}
