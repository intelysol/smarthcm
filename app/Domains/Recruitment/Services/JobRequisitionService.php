<?php

namespace App\Domains\Recruitment\Services;

use App\Domains\Organization\Models\Position;
use App\Domains\Recruitment\Enums\RequisitionPriority;
use App\Domains\Recruitment\Enums\RequisitionStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisition;
use App\Domains\Recruitment\Models\HcmRecruitmentRequisitionApproval;
use App\Domains\WorkforcePlanning\Enums\PlanPositionStatus;
use App\Domains\WorkforcePlanning\Models\HcmWorkforcePositionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobRequisitionService
{
    public function createRequisition(array $data, int $actorId): HcmRecruitmentRequisition
    {
        // 1. Position Validation
        if (!empty($data['position_id'])) {
            $position = Position::where('id', $data['position_id'])->first();
            if ($position && $position->status === 'frozen') {
                throw ValidationException::withMessages(['position_id' => 'Cannot open job requisition for a frozen position without authorized executive override.']);
            }
        }

        // Check Workforce Planning position if linked (Epic 2.24)
        if (!empty($data['position_plan_id'])) {
            $planPosition = HcmWorkforcePositionPlan::where('id', $data['position_plan_id'])->first();
            if ($planPosition && $planPosition->status === PlanPositionStatus::FROZEN->value) {
                throw ValidationException::withMessages(['position_plan_id' => 'Cannot open requisition: The workforce planning position is currently frozen.']);
            }
        }

        return DB::transaction(function () use ($data, $actorId) {
            $requisitionNumber = 'REQ-' . strtoupper(bin2hex(random_bytes(4)));

            return HcmRecruitmentRequisition::create([
                'tenant_id' => $data['tenant_id'],
                'requisition_number' => $requisitionNumber,
                'title' => $data['title'],
                'position_id' => $data['position_id'] ?? null,
                'job_template_id' => $data['job_template_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'location_id' => $data['location_id'] ?? null,
                'hiring_manager_id' => $data['hiring_manager_id'] ?? null,
                'recruiter_id' => $data['recruiter_id'] ?? $actorId,
                'employment_type' => $data['employment_type'] ?? 'full_time',
                'openings' => $data['openings'] ?? 1,
                'priority' => $data['priority'] ?? RequisitionPriority::MEDIUM->value,
                'reason' => $data['reason'] ?? 'growth',
                'min_salary' => $data['min_salary'] ?? null,
                'max_salary' => $data['max_salary'] ?? null,
                'budget_amount' => $data['budget_amount'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
                'target_start_date' => $data['target_start_date'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => RequisitionStatus::DRAFT->value,
                'is_confidential' => $data['is_confidential'] ?? false,
                'workforce_plan_id' => $data['workforce_plan_id'] ?? null,
                'position_plan_id' => $data['position_plan_id'] ?? null,
                'hiring_plan_id' => $data['hiring_plan_id'] ?? null,
            ]);
        });
    }

    public function submitRequisition(HcmRecruitmentRequisition $requisition, int $actorId): HcmRecruitmentRequisition
    {
        if ($requisition->status !== RequisitionStatus::DRAFT->value) {
            throw ValidationException::withMessages(['status' => 'Only draft requisitions can be submitted for review.']);
        }

        $requisition->update(['status' => RequisitionStatus::UNDER_REVIEW->value]);

        HcmRecruitmentRequisitionApproval::create([
            'tenant_id' => $requisition->tenant_id,
            'requisition_id' => $requisition->id,
            'approver_id' => $actorId,
            'stage' => 'department_head',
            'status' => 'pending',
        ]);

        return $requisition;
    }

    public function approveRequisition(HcmRecruitmentRequisition $requisition, int $approverId, ?string $comments = null): HcmRecruitmentRequisition
    {
        $approval = $requisition->approvals()->where('status', 'pending')->first();
        if ($approval) {
            $approval->update([
                'status' => 'approved',
                'comments' => $comments,
                'acted_at' => now(),
            ]);
        }

        $requisition->update([
            'status' => RequisitionStatus::OPEN->value,
            'approved_at' => now(),
        ]);

        return $requisition;
    }

    public function closeRequisition(HcmRecruitmentRequisition $requisition, string $reason = 'filled'): HcmRecruitmentRequisition
    {
        $status = $reason === 'filled' ? RequisitionStatus::FILLED->value : RequisitionStatus::CLOSED->value;
        $requisition->update([
            'status' => $status,
            'closed_at' => now(),
        ]);

        return $requisition;
    }
}
