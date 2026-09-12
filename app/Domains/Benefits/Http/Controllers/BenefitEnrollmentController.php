<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\BenefitEnrollment;
use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Requests\StoreBenefitEnrollmentRequest;
use App\Domains\Benefits\Services\BenefitEnrollmentService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitEnrollmentController extends Controller
{
    public function __construct(
        protected BenefitEnrollmentService $enrollmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $enrollments = BenefitEnrollment::where('tenant_id', $tenantId)
            ->with(['employee', 'plan', 'window', 'dependents', 'beneficiaries'])
            ->latest()
            ->paginate(25);

        return response()->json($enrollments);
    }

    public function store(StoreBenefitEnrollmentRequest $request): JsonResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));
        $plan = BenefitPlan::findOrFail($request->validated('benefit_plan_id'));

        $enrollment = $this->enrollmentService->enroll($employee, $plan, $request->validated());
        return response()->json($enrollment, 201);
    }

    public function approve(Request $request, BenefitEnrollment $enrollment): JsonResponse
    {
        $approved = $this->enrollmentService->approveEnrollment($enrollment, $request->user());
        return response()->json($approved);
    }
}
