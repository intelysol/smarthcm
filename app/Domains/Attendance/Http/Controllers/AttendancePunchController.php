<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\AttendanceDevice;
use App\Domains\Attendance\Requests\AttendancePunchRequest;
use App\Domains\Attendance\Services\AttendanceDeviceService;
use App\Domains\Attendance\Services\AttendanceProcessor;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AttendancePunchController extends Controller
{
    public function __construct(
        protected AttendanceDeviceService $deviceService,
        protected AttendanceProcessor $processor
    ) {}

    public function punch(AttendancePunchRequest $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $employeeId = $request->input('employee_id');
        $employee = null;

        if ($employeeId) {
            $employee = Employee::query()->where('tenant_id', $tenantId)->findOrFail($employeeId);
        } else {
            // Find current employee by logged-in user
            $employee = Employee::query()->where('tenant_id', $tenantId)->where('user_id', $user->id)->firstOrFail();
        }

        $device = null;
        if ($request->filled('device_id')) {
            $device = AttendanceDevice::query()->where('tenant_id', $tenantId)->find($request->input('device_id'));
        }

        $timestamp = $request->input('timestamp') ?: now()->toIso8601String();
        $eventType = strtoupper($request->input('event_type'));

        $result = $this->deviceService->ingestRawEvents($tenantId, $device, [
            [
                'employee_identifier' => $employee->employee_number ?? $employee->employee_code,
                'timestamp' => $timestamp,
                'event_type' => $eventType,
                'payload' => [
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'source' => 'mobile_portal',
                ],
            ],
        ], 'mobile');

        // Trigger on-the-fly session calculation for date
        $session = $this->processor->processEmployeeDate($employee, $timestamp);

        return response()->json([
            'message' => 'Punch recorded and attendance session updated.',
            'event' => $result,
            'session' => $session->load(['shift', 'sessionBreaks', 'exceptions']),
        ]);
    }
}
