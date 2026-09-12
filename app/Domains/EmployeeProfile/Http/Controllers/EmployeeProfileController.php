<?php

namespace App\Domains\EmployeeProfile\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Models\EmployeeProfilePreference;
use App\Domains\EmployeeProfile\Services\EmployeeProfileService;
use App\Domains\EmployeeProfile\Services\EmployeeTimelineService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    public function __construct(
        protected EmployeeProfileService $profileService,
        protected EmployeeTimelineService $timelineService
    ) {
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $header = $this->profileService->getProfileHeader($employee, $request->user());

        return response()->json([
            'success' => true,
            'data' => $header,
        ]);
    }

    public function summary(Request $request, string $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $summary = $this->profileService->getProfileSummary($employee, $request->user());

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    public function timeline(Request $request, string $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $events = $this->timelineService->getTimelineEvents($employee, $request->user());

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function updatePreferences(Request $request, string $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $validated = $request->validate([
            'show_personal_email' => 'nullable|boolean',
            'show_personal_phone' => 'nullable|boolean',
            'show_birthday' => 'nullable|boolean',
            'theme_preference' => 'nullable|string|in:light,dark,system',
        ]);

        $pref = EmployeeProfilePreference::updateOrCreate(
            ['employee_id' => $employee->id],
            array_merge($validated, ['tenant_id' => $employee->tenant_id])
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile preferences updated successfully.',
            'data' => $pref,
        ]);
    }
}
