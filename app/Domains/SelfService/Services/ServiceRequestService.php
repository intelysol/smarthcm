<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCase;
use App\Domains\SelfService\Enums\ServiceCommentType;
use App\Domains\SelfService\Enums\ServiceLinkType;
use App\Domains\SelfService\Enums\ServiceRequestPriority;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Events\ServiceRequestClosed;
use App\Domains\SelfService\Events\ServiceRequestCreated;
use App\Domains\SelfService\Events\ServiceRequestReopened;
use App\Domains\SelfService\Events\ServiceRequestResolved;
use App\Domains\SelfService\Events\ServiceRequestSubmitted;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestLink;
use App\Domains\SelfService\Models\HrServiceRequestStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceRequestService
{
    public function __construct(
        protected RequestAssignmentService $assignmentService,
        protected RequestSlaService $slaService
    ) {}

    public function createRequest(Employee $employee, HrServiceDefinition $service, array $data, ?User $actor = null): HrServiceRequest
    {
        return DB::transaction(function () use ($employee, $service, $data, $actor) {
            $version = $service->versions()->where('is_active', true)->orderBy('version_number', 'desc')->first();
            $requestNumber = $this->generateRequestNumber($employee->tenant_id);

            // Authoritative server-side context resolution from Core HR records
            $companyId = $employee->company_id;
            $departmentId = $employee->department_id;
            $branchId = $employee->branch_id;
            $costCenterId = $employee->cost_center_id;
            $reportingManagerId = $employee->reporting_manager_id ?? $employee->current_manager_employee_id ?? $employee->reports_to;

            $request = HrServiceRequest::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'hr_service_definition_id' => $service->id,
                'hr_service_version_id' => $version?->id,
                'request_number' => $requestNumber,
                'subject' => $data['subject'] ?? $service->name,
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? ServiceRequestPriority::NORMAL->value,
                'status' => ServiceRequestStatus::DRAFT->value,
                'confidentiality_level' => $service->confidentiality_level ?? 'normal',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'department_id' => $departmentId,
                'cost_center_id' => $costCenterId,
                'reporting_manager_id' => $reportingManagerId,
                'form_data' => $data['form_data'] ?? [],
                'source_domain_module' => $data['source_domain_module'] ?? null,
                'source_entity_type' => $data['source_entity_type'] ?? null,
                'source_entity_id' => $data['source_entity_id'] ?? null,
            ]);

            // Save individual dynamic form field records for fast searching & metadata indexing
            if (!empty($data['form_data']) && is_array($data['form_data'])) {
                $formSchema = $version?->formDefinition?->schema ?? [];
                $labelsByKey = collect($formSchema)->pluck('label', 'key')->all();
                $typesByKey = collect($formSchema)->pluck('type', 'key')->all();

                foreach ($data['form_data'] as $key => $val) {
                    $request->fields()->create([
                        'tenant_id' => $request->tenant_id,
                        'field_key' => $key,
                        'field_label' => $labelsByKey[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                        'field_type' => $typesByKey[$key] ?? 'text',
                        'field_value' => is_scalar($val) ? (string) $val : json_encode($val),
                        'raw_value' => is_array($val) ? $val : null,
                    ]);
                }
            }

            // Record creation status history
            $this->recordStatusHistory($request, null, ServiceRequestStatus::DRAFT->value, $actor, 'Request created in draft state.');

            event(new ServiceRequestCreated($request));

            return $request->fresh(['fields', 'employee', 'service']);
        });
    }

    public function submitRequest(HrServiceRequest $request, ?User $actor = null): HrServiceRequest
    {
        return DB::transaction(function () use ($request, $actor) {
            $fromStatus = $request->status;
            $toStatus = ServiceRequestStatus::SUBMITTED->value;

            $request->update([
                'status' => $toStatus,
            ]);

            $this->recordStatusHistory($request, $fromStatus, $toStatus, $actor, 'Request submitted by employee.');

            // Apply automatic queue & agent assignment
            $this->assignmentService->routeAndAssign($request);

            // Initialize SLA Tracking
            $this->slaService->initializeSla($request);

            event(new ServiceRequestSubmitted($request));

            return $request->fresh(['assignedQueue', 'assignedUser', 'slaInstance']);
        });
    }

    public function transitionStatus(HrServiceRequest $request, string $newStatus, ?User $actor = null, ?string $comment = null): HrServiceRequest
    {
        return DB::transaction(function () use ($request, $newStatus, $actor, $comment) {
            $fromStatus = $request->status;

            $updateData = ['status' => $newStatus];

            if ($newStatus === ServiceRequestStatus::RESOLVED->value && ! $request->resolved_at) {
                $updateData['resolved_at'] = now();
                $this->slaService->markResolutionMet($request);
                event(new ServiceRequestResolved($request));
            } elseif ($newStatus === ServiceRequestStatus::CLOSED->value && ! $request->closed_at) {
                $updateData['closed_at'] = now();
                event(new ServiceRequestClosed($request));
            } elseif ($newStatus === ServiceRequestStatus::WAITING_FOR_EMPLOYEE->value) {
                $this->slaService->pauseSla($request, 'Awaiting additional info from employee');
            } elseif ($fromStatus === ServiceRequestStatus::WAITING_FOR_EMPLOYEE->value && $newStatus === ServiceRequestStatus::IN_PROGRESS->value) {
                $this->slaService->resumeSla($request);
            }

            $request->update($updateData);

            $this->recordStatusHistory($request, $fromStatus, $newStatus, $actor, $comment);

            return $request->fresh();
        });
    }

    public function reopenRequest(HrServiceRequest $request, User $actor, string $reason): HrServiceRequest
    {
        if (! $request->service?->allow_reopen) {
            throw ValidationException::withMessages([
                'request' => 'Reopening is not permitted for this service.',
            ]);
        }

        $windowDays = $request->service->reopen_window_days ?? 7;
        if ($request->resolved_at && Carbon::parse($request->resolved_at)->addDays($windowDays)->isPast()) {
            throw ValidationException::withMessages([
                'request' => "This request exceeded the allowed reopen window of {$windowDays} days.",
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $reason) {
            $fromStatus = $request->status;
            $toStatus = ServiceRequestStatus::REOPENED->value;

            $request->update([
                'status' => $toStatus,
                'resolved_at' => null,
                'closed_at' => null,
            ]);

            $this->recordStatusHistory($request, $fromStatus, $toStatus, $actor, "Reopened: {$reason}");

            // Post public comment explaining reopen
            $request->comments()->create([
                'tenant_id' => $request->tenant_id,
                'user_id' => $actor->id,
                'comment_type' => ServiceCommentType::PUBLIC->value,
                'message' => "Request reopened: {$reason}",
            ]);

            event(new ServiceRequestReopened($request));

            return $request->fresh();
        });
    }

    public function linkRequests(HrServiceRequest $parent, HrServiceRequest $child, string $linkType = 'related', ?string $notes = null): HrServiceRequestLink
    {
        return HrServiceRequestLink::create([
            'tenant_id' => $parent->tenant_id,
            'parent_request_id' => $parent->id,
            'child_request_id' => $child->id,
            'link_type' => $linkType,
            'notes' => $notes,
        ]);
    }

    public function mergeRequests(HrServiceRequest $masterRequest, HrServiceRequest $duplicateRequest, User $actor, ?string $reason = null): HrServiceRequest
    {
        return DB::transaction(function () use ($masterRequest, $duplicateRequest, $actor, $reason) {
            // Link as duplicate
            $this->linkRequests($masterRequest, $duplicateRequest, ServiceLinkType::DUPLICATED_BY->value, $reason ?? "Merged into {$masterRequest->request_number}");

            // Close duplicate request
            $this->transitionStatus(
                $duplicateRequest,
                ServiceRequestStatus::CLOSED->value,
                $actor,
                "Closed as duplicate. Merged into master ticket {$masterRequest->request_number}."
            );

            // Add internal note to master request
            $masterRequest->comments()->create([
                'tenant_id' => $masterRequest->tenant_id,
                'user_id' => $actor->id,
                'comment_type' => ServiceCommentType::INTERNAL->value,
                'message' => "Ticket {$duplicateRequest->request_number} was merged into this request. Reason: " . ($reason ?? 'Duplicate inquiry'),
            ]);

            return $masterRequest->fresh();
        });
    }

    public function convertToHrCase(HrServiceRequest $request, User $actor, array $caseData): EmployeeRelationCase
    {
        return DB::transaction(function () use ($request, $actor, $caseData) {
            $caseNumber = 'ER-CASE-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
            $caseTypeId = $caseData['case_type_id'] ?? \App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType::firstOrCreate(
                ['tenant_id' => $request->tenant_id, 'code' => 'GEN-INQ'],
                ['name' => 'General Inquiry', 'category' => 'general', 'status' => 'active']
            )->id;

            $erCase = EmployeeRelationCase::create([
                'tenant_id' => $request->tenant_id,
                'case_number' => $caseNumber,
                'title' => $caseData['title'] ?? $request->subject,
                'summary' => $caseData['summary'] ?? ($caseData['description'] ?? ($request->description ?? $request->subject)),
                'case_type_id' => $caseTypeId,
                'severity' => $caseData['severity'] ?? 'medium',
                'status' => 'intake',
                'confidentiality_level' => 'confidential',
                'lead_investigator_id' => $actor->id,
            ]);

            // Link ER Case in Service Request
            $request->update([
                'employee_relation_case_id' => $erCase->id,
                'status' => ServiceRequestStatus::RESOLVED->value,
                'resolved_at' => now(),
            ]);

            $this->recordStatusHistory(
                $request,
                $request->status,
                ServiceRequestStatus::RESOLVED->value,
                $actor,
                "Escalated and converted to formal Employee Relations Case #{$caseNumber}"
            );

            $request->comments()->create([
                'tenant_id' => $request->tenant_id,
                'user_id' => $actor->id,
                'comment_type' => ServiceCommentType::INTERNAL->value,
                'message' => "Escalated to confidential HR Case #{$caseNumber}. Service request marked as resolved to prevent duplicate tracking.",
            ]);

            return $erCase;
        });
    }

    public function recordStatusHistory(HrServiceRequest $request, ?string $fromStatus, string $toStatus, ?User $actor = null, ?string $comment = null): HrServiceRequestStatusHistory
    {
        return HrServiceRequestStatusHistory::create([
            'tenant_id' => $request->tenant_id,
            'hr_service_request_id' => $request->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_user_id' => $actor?->id,
            'comment' => $comment,
            'changed_at' => now(),
        ]);
    }

    protected function generateRequestNumber(string $tenantId): string
    {
        $year = date('Y');
        $random = strtoupper(substr(uniqid(), -6));
        return "HR-REQ-{$year}-{$random}";
    }
}
