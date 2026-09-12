<?php

namespace App\Domains\Employee\Services;

use App\Domains\Employee\Models\Employee;

class EmployeeTimelineService
{
    public function record(Employee $employee, string $eventType, string $title, ?array $oldValues, ?array $newValues, int $actorId): void
    {
        $employee->timelines()->create([
            'tenant_id' => $employee->tenant_id,
            'event_type' => $eventType,
            'title' => $title,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'occurred_at' => now(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }
}
