<?php

namespace App\Domains\EmployeeExperience\Services;

use App\Domains\EmployeeExperience\Models\HcmExperienceAudit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class EmployeeExperienceAuditService
{
    public function log(
        string $tenantId,
        string $employeeId,
        string $actionType,
        ?string $actorUserId = null,
        ?string $targetType = null,
        ?string $targetId = null,
        array $metadata = []
    ): HcmExperienceAudit {
        return HcmExperienceAudit::create([
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorUserId,
            'employee_id' => $employeeId,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'System',
            'occurred_at' => Carbon::now(),
        ]);
    }
}
