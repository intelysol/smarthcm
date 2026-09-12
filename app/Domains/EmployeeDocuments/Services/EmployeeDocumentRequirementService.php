<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\RequirementStatus;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentAudit;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequirement;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EmployeeDocumentRequirementService
{
    public function assignRequirement(Employee $employee, HcmDocumentType $docType, bool $isMandatory = true, ?string $dueDate = null): EmployeeDocumentRequirement
    {
        return EmployeeDocumentRequirement::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'document_type_id' => $docType->id,
            ],
            [
                'tenant_id' => $employee->tenant_id,
                'is_mandatory' => $isMandatory,
                'status' => $isMandatory ? RequirementStatus::REQUIRED->value : RequirementStatus::OPTIONAL->value,
                'due_date' => $dueDate,
            ]
        );
    }

    public function evaluateCompleteness(Employee $employee): array
    {
        $requirements = EmployeeDocumentRequirement::where('employee_id', $employee->id)
            ->with(['documentType.category', 'employeeDocument'])
            ->get();

        $mandatoryReqs = $requirements->where('is_mandatory', true);
        $totalRequired = $mandatoryReqs->count();

        $verifiedCount = $mandatoryReqs->filter(fn ($r) => in_array($r->status, [RequirementStatus::VERIFIED->value, RequirementStatus::WAIVED->value]))->count();
        $submittedCount = $mandatoryReqs->filter(fn ($r) => $r->status === RequirementStatus::SUBMITTED->value)->count();
        $missingCount = $mandatoryReqs->filter(fn ($r) => in_array($r->status, [RequirementStatus::REQUIRED->value, RequirementStatus::REJECTED->value, RequirementStatus::EXPIRED->value]))->count();

        $completenessScore = $totalRequired > 0
            ? round(($verifiedCount / $totalRequired) * 100, 1)
            : 100.0;

        return [
            'employee_id' => $employee->id,
            'total_required' => $totalRequired,
            'verified_count' => $verifiedCount,
            'submitted_count' => $submittedCount,
            'missing_count' => $missingCount,
            'completeness_score' => $completenessScore,
            'requirements' => $requirements,
        ];
    }

    public function waiveRequirement(EmployeeDocumentRequirement $requirement, User $actor, string $reason): EmployeeDocumentRequirement
    {
        $canWaive = $actor->is_platform_admin
            || (method_exists($actor, 'hasPermission') && $actor->hasPermission('employee_documents.manage_requirements'));

        if (!$canWaive) {
            throw ValidationException::withMessages([
                'requirement' => 'Unauthorized: Waiving document requirements requires "employee_documents.manage_requirements" permission.',
            ]);
        }

        $requirement->update([
            'status' => RequirementStatus::WAIVED->value,
            'waived_by' => $actor->id,
            'waived_at' => now(),
            'waiver_reason' => $reason,
        ]);

        EmployeeDocumentAudit::create([
            'tenant_id' => $requirement->tenant_id,
            'employee_document_id' => $requirement->employee_document_id,
            'actor_id' => $actor->id,
            'event_name' => 'requirement_waived',
            'reason' => "Document requirement '{$requirement->documentType->name}' waived: {$reason}",
        ]);

        return $requirement;
    }
}
