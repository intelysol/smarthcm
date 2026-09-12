<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Http\Controllers;

use App\Domains\HealthSafety\Models\HcmMedicalAssessment;
use App\Domains\HealthSafety\Services\HealthSecurityService;
use App\Domains\HealthSafety\Services\MedicalAssessmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalAssessmentController extends Controller
{
    public function __construct(
        protected MedicalAssessmentService $service,
        protected HealthSecurityService $securityService
    ) {}

    public function schedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'requirement_id' => 'nullable|uuid',
            'assessment_type' => 'required|in:pre_placement,periodic,return_to_work,post_incident,fitness_for_duty,special_surveillance',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|string',
            'provider_id' => 'nullable|uuid',
            'clinic_name' => 'nullable|string|max:255',
            'examiner_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()?->tenant_id ?? 'default';

        $assessment = $this->service->scheduleAssessment($validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Medical assessment scheduled successfully.',
            'data' => $assessment,
        ], 201);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $assessment = HcmMedicalAssessment::findOrFail($id);

        $validated = $request->validate([
            'actual_date' => 'required|date',
            'fitness_outcome' => 'required|in:fit,fit_with_restrictions,unfit,pending_results',
            'medical_notes' => 'nullable|string',
            'examiner_name' => 'nullable|string|max:255',
            'examiner_registration_no' => 'nullable|string|max:255',
            'document_id' => 'nullable|uuid',
        ]);

        $updated = $this->service->completeAssessment($assessment, $validated, $request->user()?->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Assessment completed and result recorded.',
            'data' => $this->securityService->maskMedicalAssessment($updated, $request->user()),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $assessment = HcmMedicalAssessment::with(['employee', 'provider', 'fitnessRecord'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $this->securityService->maskMedicalAssessment($assessment, $request->user()),
        ]);
    }
}
