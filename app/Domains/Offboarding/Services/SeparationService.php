<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Offboarding\Enums\SeparationStatus;
use App\Domains\Offboarding\Models\SeparationAudit;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Domains\Offboarding\Models\SeparationType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeparationService
{
    public function __construct(
        protected ?SeparationValidationService $validationService = null,
        protected ?SeparationNoticePeriodService $noticeService = null,
        protected ?SeparationImpactService $impactService = null,
        protected ?SeparationClearanceService $clearanceService = null,
        protected ?SeparationExecutionService $executionService = null
    ) {
        $this->validationService = $validationService ?? new SeparationValidationService();
        $this->noticeService = $noticeService ?? new SeparationNoticePeriodService();
        $this->impactService = $impactService ?? new SeparationImpactService();
        $this->clearanceService = $clearanceService ?? new SeparationClearanceService();
        $this->executionService = $executionService ?? new SeparationExecutionService();
    }

    public function generateRequestNumber(string $tenantId): string
    {
        $year = date('Y');
        $count = SeparationRequest::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('SEP-%s-%06d', $year, $count);
    }

    public function createRequest(User $user, array $data): SeparationRequest
    {
        $employee = Employee::findOrFail($data['employee_id']);
        $separationType = SeparationType::findOrFail($data['separation_type_id']);
        $proposedLwd = Carbon::parse($data['proposed_last_working_day']);
        $effectiveDate = isset($data['effective_date']) ? Carbon::parse($data['effective_date']) : $proposedLwd;

        // Validation (e.g. Involuntary termination requires permissions)
        $this->validationService->validateRequestCreation($user, $employee, $separationType, $data);

        return DB::transaction(function () use ($user, $employee, $separationType, $proposedLwd, $effectiveDate, $data) {
            $requestNumber = $this->generateRequestNumber($user->tenant_id);

            $request = SeparationRequest::create([
                'tenant_id' => $user->tenant_id,
                'employee_id' => $employee->id,
                'separation_type_id' => $separationType->id,
                'request_number' => $requestNumber,
                'status' => SeparationStatus::DRAFT->value,
                'requested_by' => $user->id,
                'requested_at' => now(),
                'notice_start_date' => $data['notice_start_date'] ?? now()->toDateString(),
                'proposed_last_working_day' => $proposedLwd->toDateString(),
                'effective_date' => $effectiveDate->toDateString(),
                'reason' => $data['reason'] ?? null,
                'comments' => $data['comments'] ?? null,
                'source' => $data['source'] ?? 'self_service',
                'er_case_reference_id' => $data['er_case_reference_id'] ?? null,
                'is_garden_leave' => $data['is_garden_leave'] ?? false,
                'garden_leave_start_date' => $data['garden_leave_start_date'] ?? null,
                'garden_leave_end_date' => $data['garden_leave_end_date'] ?? null,
            ]);

            // Calculate Notice Period
            $this->noticeService->initializeNoticePeriod($request);

            // Calculate Impact Analysis
            $this->impactService->analyzeImpact($request);

            // Initialize Clearance Matrix
            if ($separationType->requires_clearance) {
                $this->clearanceService->initializeClearance($request);
            }

            // Audit Trail
            SeparationAudit::create([
                'tenant_id' => $user->tenant_id,
                'separation_request_id' => $request->id,
                'actor_id' => $user->id,
                'event_name' => 'created',
                'new_state' => ['request_number' => $requestNumber, 'effective_date' => $effectiveDate->toDateString()],
                'reason' => $data['reason'] ?? 'Separation request initiated',
            ]);

            return $request;
        });
    }

    public function submitRequest(SeparationRequest $request, User $user): SeparationRequest
    {
        if ($request->status !== SeparationStatus::DRAFT->value) {
            throw ValidationException::withMessages(['status' => 'Only draft separation requests can be submitted.']);
        }

        $request->update([
            'status' => SeparationStatus::PENDING_APPROVAL->value,
            'submitted_at' => now(),
        ]);

        SeparationAudit::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'actor_id' => $user->id,
            'event_name' => 'submitted',
            'reason' => 'Submitted for managerial and HR approval',
        ]);

        return $request;
    }

    public function approveRequest(SeparationRequest $request, User $approver, ?string $approvedLwd = null, ?string $comments = null): SeparationRequest
    {
        if (!in_array($request->status, [SeparationStatus::SUBMITTED->value, SeparationStatus::PENDING_APPROVAL->value, SeparationStatus::UNDER_REVIEW->value])) {
            throw ValidationException::withMessages(['status' => 'Separation request is not awaiting approval.']);
        }

        return DB::transaction(function () use ($request, $approver, $approvedLwd, $comments) {
            $lwd = $approvedLwd ? Carbon::parse($approvedLwd) : Carbon::parse($request->proposed_last_working_day);
            $isImmediate = $lwd->lessThanOrEqualTo(now()->toDateString());
            $nextStatus = $isImmediate ? SeparationStatus::READY_FOR_EXIT->value : SeparationStatus::NOTICE_PERIOD->value;

            $request->update([
                'status' => $nextStatus,
                'approved_at' => now(),
                'approved_by' => $approver->id,
                'approved_last_working_day' => $lwd->toDateString(),
                'comments' => $comments ?? $request->comments,
            ]);

            SeparationAudit::create([
                'tenant_id' => $request->tenant_id,
                'separation_request_id' => $request->id,
                'actor_id' => $approver->id,
                'event_name' => 'approved',
                'reason' => $comments ?? 'Approved by authorized manager/HR',
            ]);

            return $request;
        });
    }

    public function rejectRequest(SeparationRequest $request, User $rejecter, string $reason): SeparationRequest
    {
        $request->update([
            'status' => SeparationStatus::REJECTED->value,
            'comments' => $reason,
        ]);

        SeparationAudit::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'actor_id' => $rejecter->id,
            'event_name' => 'rejected',
            'reason' => $reason,
        ]);

        return $request;
    }

    public function withdrawRequest(SeparationRequest $request, User $user, string $reason): SeparationRequest
    {
        // Enforce withdrawal policy via validation service
        $this->validationService->validateWithdrawal($request, $user);

        $request->update([
            'status' => SeparationStatus::CANCELLED->value,
            'comments' => "Withdrawn by employee: {$reason}",
        ]);

        SeparationAudit::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'actor_id' => $user->id,
            'event_name' => 'withdrawn',
            'reason' => $reason,
        ]);

        return $request;
    }

    public function cancelRequest(SeparationRequest $request, User $user, string $reason): SeparationRequest
    {
        if ($request->status === SeparationStatus::EXITED->value) {
            throw ValidationException::withMessages(['status' => 'Exited employees cannot be cancelled; use formal reinstatement instead.']);
        }

        $request->update([
            'status' => SeparationStatus::CANCELLED->value,
            'comments' => $reason,
        ]);

        SeparationAudit::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'actor_id' => $user->id,
            'event_name' => 'cancelled',
            'reason' => $reason,
        ]);

        return $request;
    }
}
