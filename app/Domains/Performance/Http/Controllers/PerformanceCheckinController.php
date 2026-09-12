<?php

declare(strict_types=1);

namespace App\Domains\Performance\Http\Controllers;

use App\Domains\Performance\Models\PerformanceCheckin;
use App\Domains\Performance\Services\PerformanceCheckinService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceCheckinController extends Controller
{
    public function index(Request $request, PerformanceCheckinService $service): JsonResponse
    {
        $employeeId = (string) $request->input('employee_id');
        $cycleId = $request->input('cycle_id');

        $checkins = $service->getEmployeeCheckins($employeeId, $cycleId ? (string) $cycleId : null);
        return response()->json(['data' => $checkins]);
    }

    public function store(Request $request, PerformanceCheckinService $service): JsonResponse
    {
        $data = $request->validate([
            'tenant_id' => ['required', 'string'],
            'cycle_id' => ['required', 'string'],
            'employee_id' => ['required', 'string'],
            'manager_id' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date'],
            'agenda_items' => ['nullable', 'array'],
            'summary' => ['nullable', 'string'],
        ]);

        $checkin = $service->scheduleCheckin($data, $request->user());
        return response()->json(['data' => $checkin], 201);
    }

    public function complete(Request $request, PerformanceCheckin $checkin, PerformanceCheckinService $service): JsonResponse
    {
        $data = $request->validate([
            'summary' => ['nullable', 'string'],
            'employee_comment' => ['nullable', 'string'],
            'manager_comment' => ['nullable', 'string'],
            'agenda_items' => ['nullable', 'array'],
        ]);

        $completed = $service->completeCheckin($checkin, $data, $request->user());
        return response()->json(['data' => $completed]);
    }
}
