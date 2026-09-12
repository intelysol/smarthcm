<?php

namespace App\Domains\WorkforceIntelligence\Contracts;

use App\Domains\WorkforceIntelligence\Models\CommandCenterKpi;
use App\Domains\WorkforceIntelligence\Models\CommandCenterKpiValue;

interface KpiOrchestrationInterface
{
    public function registerKpi(array $data): CommandCenterKpi;

    public function calculateKpi(string $kpiCode, string $tenantId, ?string $departmentId, string $periodKey): CommandCenterKpiValue;

    public function refreshAllKpis(string $tenantId, ?string $periodKey = null): array;
}
