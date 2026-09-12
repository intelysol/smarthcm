<?php

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\HcmTimeAllocation;
use App\Domains\Attendance\Models\Timesheet;
use App\Domains\Attendance\Models\TimesheetEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TimesheetProjectAllocationService
{
    /**
     * Allocate project/task time for a timesheet entry.
     * Validates that allocated minutes do not exceed worked minutes.
     */
    public function allocateTime(
        string $tenantId,
        string $timesheetId,
        string $employeeId,
        string $allocationDate,
        array $allocations,
        ?string $timesheetEntryId = null
    ): Collection {
        $timesheet = Timesheet::where('tenant_id', $tenantId)->findOrFail($timesheetId);

        $totalAllocatedMinutes = array_sum(array_column($allocations, 'allocated_minutes'));

        if ($timesheetEntryId) {
            $entry = TimesheetEntry::where('tenant_id', $tenantId)->findOrFail($timesheetEntryId);
            if ($entry->worked_minutes > 0 && $totalAllocatedMinutes > $entry->worked_minutes) {
                throw new InvalidArgumentException(
                    "Total allocated minutes ({$totalAllocatedMinutes}m) exceeds worked minutes ({$entry->worked_minutes}m)."
                );
            }
        }

        $records = collect();

        foreach ($allocations as $item) {
            $record = HcmTimeAllocation::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'timesheet_id' => $timesheet->id,
                'timesheet_entry_id' => $timesheetEntryId,
                'employee_id' => $employeeId,
                'allocation_date' => $allocationDate,
                'project_id' => $item['project_id'] ?? null,
                'project_code' => $item['project_code'] ?? null,
                'task_id' => $item['task_id'] ?? null,
                'task_name' => $item['task_name'] ?? null,
                'cost_center_id' => $item['cost_center_id'] ?? null,
                'client_id' => $item['client_id'] ?? null,
                'work_type' => $item['work_type'] ?? 'project',
                'allocated_minutes' => (int) ($item['allocated_minutes'] ?? 0),
                'is_billable' => (bool) ($item['is_billable'] ?? true),
                'description' => $item['description'] ?? null,
            ]);

            $records->push($record);
        }

        return $records;
    }

    /**
     * Get summary breakdown by project and cost center for a timesheet.
     */
    public function getTimesheetAllocationSummary(string $timesheetId): array
    {
        $allocations = HcmTimeAllocation::where('timesheet_id', $timesheetId)->get();

        $byProject = $allocations->groupBy('project_code')->map(fn ($group) => [
            'project_code' => $group->first()->project_code ?? 'UNASSIGNED',
            'total_minutes' => $group->sum('allocated_minutes'),
            'billable_minutes' => $group->where('is_billable', true)->sum('allocated_minutes'),
            'non_billable_minutes' => $group->where('is_billable', false)->sum('allocated_minutes'),
        ])->values();

        $byCostCenter = $allocations->groupBy('cost_center_id')->map(fn ($group) => [
            'cost_center_id' => $group->first()->cost_center_id,
            'total_minutes' => $group->sum('allocated_minutes'),
        ])->values();

        return [
            'total_allocated_minutes' => $allocations->sum('allocated_minutes'),
            'total_billable_minutes' => $allocations->where('is_billable', true)->sum('allocated_minutes'),
            'by_project' => $byProject->toArray(),
            'by_cost_center' => $byCostCenter->toArray(),
        ];
    }
}