<?php

namespace App\Domains\WorkforceIntelligence\Services;

use App\Domains\WorkforceIntelligence\Models\CommandCenterAlert;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WorkforceAlertService
{
    public function generateAlert(array $data): CommandCenterAlert
    {
        return CommandCenterAlert::create([
            'tenant_id' => $data['tenant_id'],
            'department_id' => $data['department_id'] ?? null,
            'alert_code' => $data['alert_code'] ?? 'ALT-' . Str::upper(Str::random(6)),
            'severity' => $data['severity'] ?? 'INFO',
            'category' => $data['category'] ?? 'OPERATIONS',
            'title' => $data['title'],
            'message' => $data['message'],
            'source_module' => $data['source_module'] ?? 'COMMAND_CENTER',
            'action_url' => $data['action_url'] ?? null,
            'status' => 'ACTIVE',
            'expires_at' => $data['expires_at'] ?? Carbon::now()->addDays(7),
        ]);
    }

    public function getAlerts(string $tenantId, ?string $departmentId = null, ?string $severity = null): array
    {
        $q = CommandCenterAlert::where('tenant_id', $tenantId)->where('status', 'ACTIVE');
        if ($departmentId) {
            $q->where('department_id', $departmentId);
        }
        if ($severity) {
            $q->where('severity', $severity);
        }

        return $q->orderBy('created_at', 'desc')->get()->toArray();
    }
}
