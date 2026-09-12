<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementSurveyAudience extends EngagementModel
{
    protected $table = 'engagement_survey_audiences';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'target_type',
        'target_id',
        'filter_criteria',
    ];

    protected function casts(): array
    {
        return [
            'filter_criteria' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EngagementCampaign::class, 'campaign_id');
    }
}
