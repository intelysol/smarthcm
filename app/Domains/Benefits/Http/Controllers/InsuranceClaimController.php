<?php

namespace App\Domains\Benefits\Http\Controllers;

use App\Domains\Benefits\Models\InsuranceClaim;
use App\Domains\Benefits\Requests\StoreInsuranceClaimRequest;
use App\Domains\Benefits\Services\BenefitsAuthorizationService;
use App\Domains\Benefits\Services\InsuranceClaimService;
use App\Domains\Employee\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceClaimController extends Controller
{
    public function __construct(
        protected InsuranceClaimService $claimService,
        protected BenefitsAuthorizationService $authService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $claims = InsuranceClaim::where('tenant_id', $tenantId)
            ->with(['employee', 'policy', 'enrollment'])
            ->latest()
            ->paginate(25);

        return response()->json($claims);
    }

    public function store(StoreInsuranceClaimRequest $request): JsonResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));
        $claim = $this->claimService->submitClaim($employee, $request->validated());

        return response()->json($claim, 201);
    }

    public function show(Request $request, InsuranceClaim $claim): JsonResponse
    {
        // Enforce medical privacy
        if ($claim->is_sensitive_medical && ! $this->authService->canViewSensitiveMedicalClaim($request->user(), $claim)) {
            return response()->json(['message' => 'Unauthorized access to confidential medical claim details.'], 403);
        }

        return response()->json($claim->load(['employee', 'policy', 'lines', 'documents']));
    }

    public function approve(Request $request, InsuranceClaim $claim): JsonResponse
    {
        $request->validate(['approved_amount' => ['required', 'numeric', 'min:0']]);
        $approved = $this->claimService->approveClaim($claim, (float) $request->input('approved_amount'), $request->user());

        return response()->json($approved);
    }
}
