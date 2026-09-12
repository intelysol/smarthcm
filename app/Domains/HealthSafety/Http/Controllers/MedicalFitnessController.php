<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmMedicalFitnessRecord;
use App\Domains\HealthSafety\Services\HealthSecurityService;
use App\Domains\HealthSafety\Services\MedicalFitnessService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalFitnessController extends Controller
{
    public function __construct(
        protected MedicalFitnessService $service,
        protected HealthSecurityService $securityService
    ) {}

    public function record(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'assessment_id' => 'nullable|uuid',
            'status' => 'required|in:fit,fit_with_restrictions,temporarily_unfit,permanently_unfit',
            'effective_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:effective_date',
            'medical_provider_id' => 'nullable|uuid',
            'certifying_physician' => 'nullable|string|max:255',
            'certificate_number' => 'nullable|string|max:255',
            'document_id' => 'nullable|uuid',
            'operational_notes' => 'nullable|string',
            'clinical_notes_restricted' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $fitness = $this->service->recordFitness($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Medical fitness clearance recorded successfully.',
            'data' => $this->securityService->maskMedicalFitnessRecord($fitness, $request->user()),
        ], 201);
    }

    public function employeeFitness(Request $request, string $employeeId): JsonResponse
    {
        $active = $this->service->getActiveFitness($employeeId);
        $history = $this->service->getFitnessHistory($employeeId);

        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_fitness' => $active ? $this->securityService->maskMedicalFitnessRecord($active, $user) : null,
                'history' => $history->map(fn($item) => $this->securityService->maskMedicalFitnessRecord($item, $user)),
            ],
        ]);
    }
}
