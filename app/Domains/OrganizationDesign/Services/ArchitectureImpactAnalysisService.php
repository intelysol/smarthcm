<?php

namespace App\Domains\OrganizationDesign\Services;

use App\Domains\Compensation\Models\CompensationBand;
use App\Domains\Employee\Models\Employee;
use App\Domains\Learning\Models\LearningRequirement;
use App\Domains\Organization\Models\Position;
use App\Domains\OrganizationDesign\Models\JobProfile;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;

class ArchitectureImpactAnalysisService
{
    /**
     * Compute comprehensive cross-domain impact analysis for a Job Profile change.
     */
    public function assessJobProfileImpact(JobProfile $profile): array
    {
        $tenantId = $profile->tenant_id;
        $jobId = $profile->job_id;

        // 1. Affected Positions
        $positions = Position::where('tenant_id', $tenantId)
            ->when($jobId, fn ($q) => $q->where('job_id', $jobId))
            ->get();

        $positionIds = $positions->pluck('id')->filter()->all();

        // 2. Affected Active Employees
        $employees = Employee::where('tenant_id', $tenantId)
            ->whereIn('current_position_id', $positionIds)
            ->get();

        // 3. Affected Open Recruitment Requisitions
        $requisitions = HcmRecruitmentRequisition::where('tenant_id', $tenantId)
            ->whereIn('position_id', $positionIds)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->get();

        // 4. Affected Compensation Bands
        $compensationBands = [];
        if ($profile->job_grade_id) {
            $compensationBands = CompensationBand::where('tenant_id', $tenantId)
                ->where('job_grade_id', $profile->job_grade_id)
                ->get();
        }

        // 5. Affected Learning Requirements
        $learningRequirements = LearningRequirement::where('tenant_id', $tenantId)
            ->where(function ($q) use ($profile, $jobId) {
                if ($jobId) {
                    $q->where('job_id', $jobId);
                }
            })
            ->get();

        return [
            'job_profile_id' => $profile->id,
            'job_profile_code' => $profile->code,
            'job_profile_title' => $profile->title,
            'affected_positions_count' => $positions->count(),
            'affected_employees_count' => $employees->count(),
            'affected_requisitions_count' => $requisitions->count(),
            'affected_compensation_bands_count' => count($compensationBands),
            'affected_learning_requirements_count' => $learningRequirements->count(),
            'positions' => $positions->map(fn ($p) => ['id' => $p->id, 'position_code' => $p->position_code ?? $p->code, 'title' => $p->title]),
            'employees' => $employees->map(fn ($e) => ['id' => $e->id, 'employee_number' => $e->employee_number, 'name' => "{$e->first_name} {$e->last_name}"]),
            'requisitions' => $requisitions->map(fn ($r) => ['id' => $r->id, 'requisition_number' => $r->requisition_number, 'title' => $r->title]),
        ];
    }
}
