<?php

namespace App\Domains\WorkforceAdmin\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\WorkforceAdmin\Models\OpsCalendarEvent;

class HrCalendarService
{
    /**
     * Aggregate calendar events referencing underlying domain entities.
     */
    public function syncCalendarEvents(string $tenantId): int
    {
        $syncedCount = 0;

        // 1. Sync Employee Joining Dates
        $newHires = Employee::where('tenant_id', $tenantId)
            ->whereNotNull('joining_date')
            ->get();

        foreach ($newHires as $emp) {
            OpsCalendarEvent::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'source_domain' => 'core_hr',
                    'source_entity_type' => 'Employee',
                    'source_entity_id' => $emp->id,
                    'event_type' => 'new_hire_joining',
                ],
                [
                    'title' => "New Hire: {$emp->first_name} {$emp->last_name} Joining",
                    'event_date' => $emp->joining_date->toDateString(),
                    'employee_id' => $emp->id,
                    'metadata' => ['department_id' => $emp->department_id],
                ]
            );
            $syncedCount++;
        }

        return $syncedCount;
    }
}
