<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Services\TimesheetProjectAllocationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetAllocationController extends Controller
{
    public function store(Request $request, TimesheetProjectAllocationService $service): JsonResponse
    {
        $validated = $request->validate([
            'timesheet_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'allocation_date' => 'required|date',
            'allocations' => 'required|array',
            'timesheet_entry_id' => 'nullable|uuid',
        ]);

        $tenantId = $request->user()?->tenant_id ?? $request->input('tenant_id');

        $records = $service->allocateTime(
            $tenantId,
            $validated['timesheet_id'],
            $validated['employee_id'],
            $validated['allocation_date'],
            $validated['allocations'],
            $validated['timesheet_entry_id'] ?? null
        );

        return response()->json(['message' => 'Time allocated successfully', 'data' => $records], 201);
    }

    public function summary(string $timesheetId, TimesheetProjectAllocationService $service): JsonResponse
    {
        $summary = $service->getTimesheetAllocationSummary($timesheetId);

        return response()->json(['data' => $summary]);
    }
}