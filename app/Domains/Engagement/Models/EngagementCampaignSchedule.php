<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementCampaignSchedule extends EngagementModel
{
    protected $table = 'engagement_campaign_schedules';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'frequency',
        'cron_expression',
        'next_run_at',
        'last_run_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EngagementCampaign::class, 'campaign_id');
    }
}
