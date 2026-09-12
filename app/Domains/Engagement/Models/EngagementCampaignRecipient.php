<?php

namespace App\Domains\Engagement\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementCampaignRecipient extends EngagementModel
{
    protected $table = 'engagement_campaign_recipients';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'employee_id',
        'delivery_status',
        'invited_at',
        'reminder_count',
        'last_reminded_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'last_reminded_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminder_count' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EngagementCampaign::class, 'campaign_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
