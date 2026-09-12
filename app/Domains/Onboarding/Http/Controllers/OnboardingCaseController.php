<?php

namespace App\Domains\Onboarding\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Onboarding\Models\HcmOnboardingCase;
use App\Domains\Onboarding\Services\OnboardingCaseService;
use App\Domains\Onboarding\Services\OnboardingSecurityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingCaseController extends Controller
{
    public function __construct(
        protected OnboardingCaseService $caseService,
        protected OnboardingSecurityService $securityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = HcmOnboardingCase::where('tenant_id', $tenantId)
            ->with(['employee.department', 'templateVersion.template']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid',
            'template_version_id' => 'nullable|uuid',
            'start_date' => 'nullable|date',
            'recruitment_application_id' => 'nullable|uuid',
            'offer_id' => 'nullable|uuid',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $case = $this->caseService->initializeCaseForEmployee(
            $employee,
            array_merge($validated, ['owner_id' => $request->user()->id])
        );

        return response()->json($case, 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $case = HcmOnboardingCase::where('id', $id)
            ->with(['employee.department', 'tasks.prerequisites', 'documentRequirements', 'policyAcknowledgements', 'buddyAssignment.buddy', 'probation'])
            ->firstOrFail();

        $this->securityService->authorizeCaseAccess($request->user(), $case);

        return response()->json($case);
    }
}
