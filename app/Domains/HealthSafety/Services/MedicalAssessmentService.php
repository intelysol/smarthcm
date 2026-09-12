<?php

namespace App\Domains\HealthSafety\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\HealthSafety\Models\HcmMedicalAssessment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class MedicalAssessmentService
{
    /**
     * Schedule a medical examination.
     */
    public function scheduleAssessment(string|array $employeeIdOrData, array $data = [], ?User $actor = null): HcmMedicalAssessment
    {
        if (is_array($employeeIdOrData)) {
            $data = $employeeIdOrData;
            $employeeId = $data['employee_id'];
        } else {
            $employeeId = $employeeIdOrData;
        }

        $employee = Employee::findOrFail($employeeId);

        return HcmMedicalAssessment::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employeeId,
            'health_requirement_id' => $data['health_requirement_id'] ?? null,
            'medical_provider_id' => $data['medical_provider_id'] ?? ($data['provider_id'] ?? null),
            'assessment_type' => $data['assessment_type'] ?? 'periodic',
            'requested_date' => $data['requested_date'] ?? now()->toDateString(),
            'scheduled_date' => $data['scheduled_date'],
            'status' => 'scheduled',
            'scheduled_by' => $actor?->id,
            'operational_notes' => $data['operational_notes'] ?? ($data['notes'] ?? null),
        ]);
    }

    /**
     * Complete an assessment and log result reference.
     */
    public function completeAssessment(HcmMedicalAssessment|string $assessmentOrId, array $data, ?User $actor = null): HcmMedicalAssessment
    {
        $assessment = $assessmentOrId instanceof HcmMedicalAssessment
            ? $assessmentOrId
            : HcmMedicalAssessment::findOrFail($assessmentOrId);

        $assessment->update([
            'status' => 'completed',
            'completed_date' => $data['completed_date'] ?? ($data['actual_date'] ?? now()->toDateString()),
            'document_id' => $data['document_id'] ?? $assessment->document_id,
            'operational_notes' => $data['operational_notes'] ?? ($data['medical_notes'] ?? $assessment->operational_notes),
        ]);

        // If a fitness outcome is provided, create/update medical fitness determination
        if (!empty($data['fitness_outcome'])) {
            $fitnessService = app(MedicalFitnessService::class);
            $fitnessService->recordFitness($assessment->employee_id, [
                'assessment_id' => $assessment->id,
                'medical_provider_id' => $assessment->medical_provider_id,
                'fitness_status' => $data['fitness_outcome'],
                'determined_date' => $assessment->completed_date?->toDateString() ?? now()->toDateString(),
                'summary_notes' => $data['medical_notes'] ?? null,
            ], $actor);

            $assessment->fitness_outcome = $data['fitness_outcome'];
        }

        return $assessment->fresh();
    }


    /**
     * Get all assessments for an employee.
     */
    public function getAssessments(string $employeeId): Collection
    {
        return HcmMedicalAssessment::with(['provider', 'requirement'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('scheduled_date')
            ->get();
    }
}
