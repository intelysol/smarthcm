<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Enums\SkillSource;
use App\Domains\Career\Enums\SkillVerificationStatus;
use App\Domains\Career\Events\EmployeeSkillAdded;
use App\Domains\Career\Events\EmployeeSkillExpired;
use App\Domains\Career\Events\EmployeeSkillUpdated;
use App\Domains\Career\Events\EmployeeSkillVerified;
use App\Domains\Career\Models\CareerSkill;
use App\Domains\Career\Models\EmployeeSkill;
use App\Domains\Career\Models\EmployeeSkillEvidence;
use App\Domains\Employee\Models\Employee;
use App\Domains\Events\Services\EventBus;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CareerSkillService
{
    public function __construct(
        protected AuditService $audit,
        protected EventBus $events
    ) {}

    public function addSkillToEmployee(
        Employee $employee,
        CareerSkill $skill,
        int $currentLevel = 1,
        ?int $targetLevel = null,
        string $source = 'employee',
        ?string $notes = null
    ): EmployeeSkill {
        return DB::transaction(function () use ($employee, $skill, $currentLevel, $targetLevel, $source, $notes) {
            $verificationStatus = ($source === 'system')
                ? SkillVerificationStatus::SystemVerified->value
                : SkillVerificationStatus::SelfDeclared->value;

            $nextAssessment = $skill->assessment_interval_months
                ? now()->addMonths($skill->assessment_interval_months)->toDateString()
                : null;

            $employeeSkill = EmployeeSkill::query()->updateOrCreate(
                [
                    'tenant_id' => $employee->tenant_id,
                    'employee_id' => $employee->id,
                    'skill_id' => $skill->id,
                ],
                [
                    'current_level' => $currentLevel,
                    'target_level' => $targetLevel,
                    'source' => $source,
                    'verification_status' => $verificationStatus,
                    'last_assessed_at' => now()->toDateString(),
                    'next_assessment_at' => $nextAssessment,
                    'notes' => $notes,
                ]
            );

            EmployeeSkillAdded::dispatch($employeeSkill);

            $this->audit->record(
                (string) $employee->tenant_id,
                'EmployeeSkillAdded',
                'add_skill',
                EmployeeSkill::class,
                (string) $employeeSkill->id,
                null,
                null,
                [
                    'employee_id' => $employee->id,
                    'skill_id' => $skill->id,
                    'level' => $currentLevel,
                    'source' => $source,
                ]
            );

            $this->events->publish([
                'tenant_id' => $employee->tenant_id,
                'event_type' => 'EmployeeSkillAdded',
                'event_version' => 1,
                'aggregate_type' => 'employee_skill',
                'aggregate_id' => $employeeSkill->id,
                'source_module' => 'career',
                'payload' => [
                    'employee_id' => $employee->id,
                    'skill_id' => $skill->id,
                    'current_level' => $currentLevel,
                ],
            ]);

            return $employeeSkill;
        });
    }

    public function verifySkill(
        EmployeeSkill $employeeSkill,
        Employee $verifier,
        string $status = 'manager_verified'
    ): EmployeeSkill {
        return DB::transaction(function () use ($employeeSkill, $verifier, $status) {
            $employeeSkill->update([
                'verification_status' => $status,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);

            EmployeeSkillVerified::dispatch($employeeSkill);

            $this->audit->record(
                (string) $employeeSkill->tenant_id,
                'EmployeeSkillVerified',
                'verify_skill',
                EmployeeSkill::class,
                (string) $employeeSkill->id,
                null,
                null,
                [
                    'employee_id' => $employeeSkill->employee_id,
                    'skill_id' => $employeeSkill->skill_id,
                    'verified_by' => $verifier->id,
                    'status' => $status,
                ]
            );

            return $employeeSkill->fresh();
        });
    }

    public function attachEvidence(
        EmployeeSkill $employeeSkill,
        string $evidenceType,
        string $title,
        ?string $description = null,
        ?string $documentId = null,
        ?string $referenceType = null,
        ?string $referenceId = null
    ): EmployeeSkillEvidence {
        return EmployeeSkillEvidence::query()->create([
            'tenant_id' => $employeeSkill->tenant_id,
            'employee_skill_id' => $employeeSkill->id,
            'evidence_type' => $evidenceType,
            'title' => $title,
            'description' => $description,
            'document_id' => $documentId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'verified' => false,
        ]);
    }

    public function checkSkillExpiries(string $tenantId): int
    {
        $expiredSkills = EmployeeSkill::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('next_assessment_at')
            ->where('next_assessment_at', '<', now()->toDateString())
            ->where('verification_status', '!=', 'expired')
            ->get();

        foreach ($expiredSkills as $skill) {
            $skill->update(['verification_status' => 'expired']);
            EmployeeSkillExpired::dispatch($skill);
        }

        return $expiredSkills->count();
    }
}
