<?php

namespace App\Domains\Engagement\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementParticipationToken extends EngagementModel
{
    protected $table = 'engagement_participation_tokens';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'token_hash',
        'expires_at',
        'used_at',
        'is_used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'is_used' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EngagementCampaign::class, 'campaign_id');
    }
}
