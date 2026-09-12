<?php

namespace App\Domains\WorkforceIntelligence\Contracts;

use App\Domains\WorkforceIntelligence\DTOs\ExecutiveScorecardData;
use App\Domains\WorkforceIntelligence\DTOs\WorkforceHealthIndexData;
use App\Domains\WorkforceIntelligence\DTOs\WorkforcePulseData;

interface WorkforceCommandCenterInterface
{
    public function getExecutiveScorecard(string $tenantId, ?string $departmentId = null, ?string $periodKey = null): ExecutiveScorecardData;

    public function getWorkforceHealthIndex(string $tenantId, ?string $departmentId = null, ?string $periodKey = null): WorkforceHealthIndexData;

    public function getWorkforcePulse(string $tenantId, ?string $departmentId = null): WorkforcePulseData;

    public function getConsolidatedRisks(string $tenantId, ?string $departmentId = null, ?string $category = null, ?string $severity = null): array;

    public function getPrioritizedAlerts(string $tenantId, ?string $departmentId = null, ?string $severity = null): array;

    public function getDecisionQueue(string $tenantId, ?string $departmentId = null, ?string $status = 'PENDING'): array;
}
