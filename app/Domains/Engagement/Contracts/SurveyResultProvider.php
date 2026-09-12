<?php

namespace App\Domains\Engagement\Contracts;

use App\Domains\Engagement\Models\EngagementCampaign;

interface SurveyResultProvider
{
    public function getCampaignResults(EngagementCampaign $campaign, ?string $departmentId = null, ?string $locationId = null): array;
    public function getDimensionScores(EngagementCampaign $campaign): array;
    public function calculateNps(EngagementCampaign $campaign): array;
}
