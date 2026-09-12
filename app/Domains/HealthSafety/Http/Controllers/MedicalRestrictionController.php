<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmMedicalRestriction;
use App\Domains\HealthSafety\Services\HealthSecurityService;
use App\Domains\HealthSafety\Services\MedicalRestrictionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRestrictionController extends Controller
{
    public function __construct(
        protected MedicalRestrictionService $service,
        protected HealthSecurityService $securityService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'fitness_record_id' => 'nullable|uuid',
            'restriction_type' => 'required|in:lifting_limit,sitting_standing,no_night_shifts,no_heights,no_machinery,ergonomic,chemical_avoidance,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_permanent' => 'boolean',
            'operational_description' => 'required|string',
            'medical_rationale_restricted' => 'nullable|string',
            'recommended_accommodations' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $restriction = $this->service->createRestriction($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Workplace medical restriction recorded successfully.',
            'data' => $this->securityService->maskMedicalRestriction($restriction, $request->user()),
        ], 201);
    }

    public function employeeRestrictions(Request $request, string $employeeId): JsonResponse
    {
        $restrictions = $this->service->getEmployeeRestrictions($employeeId);
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => $restrictions->map(fn($r) => $this->securityService->maskMedicalRestriction($r, $user)),
        ]);
    }

    public function lift(Request $request, string $id): JsonResponse
    {
        $restriction = HcmMedicalRestriction::findOrFail($id);
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $lifted = $this->service->liftRestriction($restriction, $validated['reason'], (int) $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Restriction lifted successfully.',
            'data' => $lifted,
        ]);
    }
}
