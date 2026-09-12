<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\WorkforceAdmin\Models\OpsException;
use App\Domains\WorkforceAdmin\Models\OpsQueueItem;

class WorkforceAdminAiAdvisoryService
{
    /**
     * Advisory-only operational insights.
     * Guardrail: never autonomously mutates or approves records.
     */
    public function generateAdvisoryInsights(string $tenantId): array
    {
        $openCriticalExceptions = OpsException::where('tenant_id', $tenantId)
            ->where('severity', 'critical')
            ->whereIn('status', ['detected', 'assigned'])
            ->count();

        $insights = [];
        $recommendations = [];

        if ($openCriticalExceptions > 0) {
            $insights[] = "Detected {$openCriticalExceptions} critical operational exceptions requiring immediate review.";
            $recommendations[] = "Prioritize investigation of critical exceptions to mitigate payroll or compliance disruption.";
        } else {
            $insights[] = "No critical operational exceptions currently detected.";
        }

        $pendingQueueCount = OpsQueueItem::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        if ($pendingQueueCount > 10) {
            $insights[] = "Operational queues have {$pendingQueueCount} items pending assignment.";
            $recommendations[] = "Distribute unassigned queue items across HR operations specialists to prevent SLA breach.";
        }

        return [
            'is_advisory_only' => true,
            'summary' => 'Workforce Administration AI Operational Advisory',
            'insights' => $insights,
            'recommendations' => $recommendations,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
