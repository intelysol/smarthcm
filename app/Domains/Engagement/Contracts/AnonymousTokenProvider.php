<?php

namespace App\Domains\Engagement\Contracts;

use App\Domains\Engagement\Models\EngagementCampaign;

interface AnonymousTokenProvider
{
    public function generateToken(EngagementCampaign $campaign): string;
    public function validateToken(EngagementCampaign $campaign, string $rawToken): bool;
    public function burnToken(EngagementCampaign $campaign, string $rawToken): void;
}
