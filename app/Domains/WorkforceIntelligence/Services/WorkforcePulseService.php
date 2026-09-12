<?php

namespace App\Domains\WorkforceIntelligence\Services;

use App\Domains\WorkforceIntelligence\DTOs\WorkforcePulseData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WorkforcePulseService
{
    public function getPulse(string $tenantId, ?string $departmentId = null): WorkforcePulseData
    {
        $headcountQuery = DB::table('employees')->where('tenant_id', $tenantId);
        if ($departmentId) {
            $headcountQuery->where('department_id', $departmentId);
        }
        $activeHeadcount = $headcountQuery->count();

        $openCriticalAlerts = DB::table('hcm_command_center_alerts')
            ->where('tenant_id', $tenantId)
            ->where('severity', 'CRITICAL')
            ->where('status', 'ACTIVE')
            ->count();

        $hotspots = [
            [
                'area' => 'Warehouse Logistics',
                'issue' => 'Overtime surge (+28%) due to seasonal throughput',
                'severity' => 'WARNING',
            ],
            [
                'area' => 'Software Engineering',
                'issue' => 'Senior Backend vacancy open > 65 days',
                'severity' => 'WARNING',
            ]
        ];

        return new WorkforcePulseData(
            activeHeadcount: $activeHeadcount,
            scheduledFteToday: (float) $activeHeadcount * 0.95,
            actualHoursWorkedToday: (float) $activeHeadcount * 7.8,
            overtimeHoursToday: (float) $activeHeadcount * 0.6,
            absenceRateToday: 2.8,
            openCriticalAlerts: $openCriticalAlerts,
            dataFreshnessTimestamp: Carbon::now()->toIso8601String(),
            hotspots: $hotspots
        );
    }
}
