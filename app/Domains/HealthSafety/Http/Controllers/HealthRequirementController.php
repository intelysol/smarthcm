<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmHealthRequirement;
use App\Domains\HealthSafety\Models\HcmHealthRequirementType;
use App\Domains\HealthSafety\Services\OccupationalHealthRequirementService;
use App\Domains\HR\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthRequirementController extends Controller
{
    public function __construct(
        protected OccupationalHealthRequirementService $service
    ) {}

    public function types(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => HcmHealthRequirementType::where('is_active', true)->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $companyId = $request->query('company_id');

        $query = HcmHealthRequirement::where('tenant_id', $tenantId)->with('requirementType');
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'type_id' => 'required|uuid',
            'title' => 'required|string|max:255',
            'applicability_type' => 'required|in:all,department,position,job_family,hazard_based,specific_employees',
            'applicability_criteria' => 'nullable|array',
            'frequency_type' => 'required|in:one_time,periodic,event_triggered',
            'frequency_interval_months' => 'nullable|integer',
            'grace_period_days' => 'nullable|integer',
            'is_mandatory' => 'boolean',
            'blocks_work_assignment' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $requirement = $this->service->createRequirement($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Occupational health requirement created successfully.',
            'data' => $requirement,
        ], 201);
    }

    public function employeeRequirements(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $requirements = $this->service->getEmployeeRequirements($employee);

        return response()->json([
            'status' => 'success',
            'data' => $requirements,
        ]);
    }

    public function evaluateApplicability(Request $request, string $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $assigned = $this->service->assignApplicableRequirements($employee);

        return response()->json([
            'status' => 'success',
            'message' => "Evaluated requirements. Assigned/refreshed {$assigned->count()} items.",
            'data' => $assigned,
        ]);
    }
}
