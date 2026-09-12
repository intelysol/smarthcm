<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Engagement\Contracts\AnonymousTokenProvider;
use App\Domains\Engagement\Models\EngagementCampaign;
use App\Domains\Engagement\Models\EngagementParticipationToken;
use Illuminate\Support\Str;

class EngagementTokenService implements AnonymousTokenProvider
{
    public function generateToken(EngagementCampaign $campaign, int $validDays = 14): string
    {
        $rawToken = Str::random(40);
        $hash = hash('sha256', $rawToken);

        EngagementParticipationToken::query()->create([
            'tenant_id' => $campaign->tenant_id,
            'campaign_id' => $campaign->id,
            'token_hash' => $hash,
            'expires_at' => now()->addDays($validDays),
            'is_used' => false,
        ]);

        return $rawToken;
    }

    public function validateToken(EngagementCampaign $campaign, string $rawToken): bool
    {
        $hash = hash('sha256', $rawToken);

        return EngagementParticipationToken::query()
            ->where('campaign_id', $campaign->id)
            ->where('token_hash', $hash)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function burnToken(EngagementCampaign $campaign, string $rawToken): void
    {
        $hash = hash('sha256', $rawToken);

        EngagementParticipationToken::query()
            ->where('campaign_id', $campaign->id)
            ->where('token_hash', $hash)
            ->where('is_used', false)
            ->update([
                'is_used' => true,
                'used_at' => now(),
            ]);
    }
}
