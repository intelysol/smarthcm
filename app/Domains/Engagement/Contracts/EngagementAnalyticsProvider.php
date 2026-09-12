<?php

namespace App\Domains\Engagement\Contracts;

interface EngagementAnalyticsProvider
{
    public function generateEngagementMetrics(string $tenantId): array;
}
