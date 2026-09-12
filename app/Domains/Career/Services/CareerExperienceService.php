<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Models\CareerJobRotation;
use App\Domains\Career\Models\CareerStretchAssignment;
use App\Domains\Career\Models\CareerTalentEvidence;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Department;
use App\Domains\Organization\Models\Job;
use App\Domains\Shared\Services\AuditService;

class CareerExperienceService
{
    public function __construct(protected AuditService $audit) {}

    public function createRotation(
        Employee $employee,
        Job $currentJob,
        Job $rotationJob,
        ?Department $department = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $objective = null
    ): CareerJobRotation {
        $rotation = CareerJobRotation::query()->create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'current_job_id' => $currentJob->id,
            'rotation_job_id' => $rotationJob->id,
            'department_id' => $department?->id,
            'start_date' => $startDate ?? now()->toDateString(),
            'end_date' => $endDate,
            'objective' => $objective,
            'status' => 'active',
        ]);

        $this->audit->record(
            (string) $employee->tenant_id,
            'CareerJobRotationCreated',
            'create_job_rotation',
            CareerJobRotation::class,
            (string) $rotation->id,
            null,
            null,
            ['employee_id' => $employee->id, 'rotation_job_id' => $rotationJob->id]
        );

        return $rotation;
    }

    public function createStretchAssignment(
        Employee $employee,
        string $title,
        ?string $description = null,
        ?Employee $leader = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?array $skillsTargeted = null
    ): CareerStretchAssignment {
        return CareerStretchAssignment::query()->create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'title' => $title,
            'description' => $description,
            'leader_employee_id' => $leader?->id,
            'start_date' => $startDate ?? now()->toDateString(),
            'end_date' => $endDate,
            'skills_targeted' => $skillsTargeted,
            'status' => 'active',
        ]);
    }

    public function recordTalentEvidence(
        Employee $employee,
        string $evidenceSource,
        string $title,
        ?string $description = null,
        ?string $sourceId = null,
        ?float $impactRating = null,
        ?Employee $recorder = null
    ): CareerTalentEvidence {
        return CareerTalentEvidence::query()->create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'evidence_source' => $evidenceSource,
            'title' => $title,
            'description' => $description,
            'source_id' => $sourceId,
            'impact_rating' => $impactRating,
            'recorded_by' => $recorder?->id,
        ]);
    }
}
