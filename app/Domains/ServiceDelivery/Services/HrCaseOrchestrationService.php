<?php

namespace App\Domains\ServiceDelivery\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Enums\ServiceCommentType;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Enums\SlaStatus;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceFeedback;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestComment;
use App\Domains\SelfService\Services\RequestAssignmentService;
use App\Domains\SelfService\Services\RequestEscalationService;
use App\Domains\SelfService\Services\RequestSlaService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HrCaseOrchestrationService
{
    public function __construct(
        protected ServiceRequestService $requestService,
        protected RequestAssignmentService $assignmentService,
        protected RequestSlaService $slaService,
        protected RequestEscalationService $escalationService
    ) {}

    /**
     * Filtered Case Inbox with multi-dimensional filtering.
     */
    public function getFilteredCases(string $tenantId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = HrServiceRequest::where('hr_service_requests.tenant_id', $tenantId)
            ->with(['employee', 'service', 'assignedQueue', 'assignedUser', 'slaInstance']);

        // 1. Status Filter
        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            if ($status === 'open') {
                $query->whereNotIn('hr_service_requests.status', [
                    ServiceRequestStatus::RESOLVED->value,
                    ServiceRequestStatus::CLOSED->value,
                    ServiceRequestStatus::CANCELLED->value,
                ]);
            } else {
                $query->where('hr_service_requests.status', $status);
            }
        }

        // 2. Priority Filter
        if (!empty($filters['priority'])) {
            $query->where('hr_service_requests.priority', strtolower($filters['priority']));
        }

        // 3. Queue Filter
        if (!empty($filters['queue_id'])) {
            $query->where('hr_service_requests.assigned_queue_id', $filters['queue_id']);
        }

        // 4. Assigned User Filter
        if (!empty($filters['assigned_to'])) {
            if ($filters['assigned_to'] === 'unassigned') {
                $query->whereNull('hr_service_requests.assigned_user_id');
            } else {
                $query->where('hr_service_requests.assigned_user_id', $filters['assigned_to']);
            }
        }

        // 5. Employee Filter
        if (!empty($filters['employee_id'])) {
            $query->where('hr_service_requests.employee_id', $filters['employee_id']);
        }

        // 6. Search Filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('hr_service_requests.request_number', 'like', "%{$search}%")
                  ->orWhere('hr_service_requests.subject', 'like', "%{$search}%")
                  ->orWhere('hr_service_requests.description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('hr_service_requests.created_at', 'desc')->paginate($perPage);
    }

    /**
     * Chronological Timeline Aggregator.
     */
    public function getCaseTimeline(HrServiceRequest $request, bool $includeInternalNotes = false): array
    {
        $timeline = [];

        // 1. Creation event
        $timeline[] = [
            'type' => 'CREATED',
            'title' => 'Case Created',
            'description' => "Case #{$request->request_number} was created by employee {$request->employee?->fullName()}.",
            'timestamp' => $request->created_at->toIso8601String(),
            'actor' => $request->employee?->fullName() ?? 'System',
        ];

        // 2. Status changes
        foreach ($request->statusHistory as $sh) {
            $timeline[] = [
                'type' => 'STATUS_CHANGE',
                'title' => 'Status Updated to ' . strtoupper($sh->to_status),
                'description' => $sh->comment ?? ("Status transitioned from " . ($sh->from_status ?? 'start') . " to {$sh->to_status}"),
                'timestamp' => Carbon::parse($sh->changed_at)->toIso8601String(),
                'actor' => $sh->changed_by_user_id ? 'HR Agent' : 'System',
            ];
        }

        // 3. Comments (Public vs Internal Notes)
        $commentsQuery = $request->comments();
        if (!$includeInternalNotes) {
            $commentsQuery->where('comment_type', ServiceCommentType::PUBLIC->value);
        }

        foreach ($commentsQuery->get() as $c) {
            $timeline[] = [
                'type' => $c->comment_type === ServiceCommentType::INTERNAL->value ? 'INTERNAL_NOTE' : 'MESSAGE',
                'title' => $c->comment_type === ServiceCommentType::INTERNAL->value ? 'Internal HR Note' : 'Message to Employee',
                'description' => $c->message,
                'timestamp' => $c->created_at->toIso8601String(),
                'actor' => $c->user_id ? 'HR Agent' : ($request->employee?->fullName() ?? 'Employee'),
                'is_internal' => $c->comment_type === ServiceCommentType::INTERNAL->value,
            ];
        }

        // 4. Assignments
        foreach ($request->assignments as $asgn) {
            $toQueueName = $asgn->toQueue?->name ?? 'Queue';
            $toUserName = $asgn->toUser?->name ?? 'Agent';
            $timeline[] = [
                'type' => 'ASSIGNMENT',
                'title' => 'Assigned',
                'description' => "Assigned to {$toQueueName}" . ($asgn->to_user_id ? " ({$toUserName})" : "") . ". Reason: " . ($asgn->reason ?? 'Routing'),
                'timestamp' => Carbon::parse($asgn->assigned_at)->toIso8601String(),
                'actor' => 'Routing Engine',
            ];
        }

        // 5. SLA Milestones
        if ($request->slaInstance) {
            foreach ($request->slaInstance->events as $slaEvent) {
                $timeline[] = [
                    'type' => 'SLA_EVENT',
                    'title' => 'SLA Event: ' . strtoupper($slaEvent->event_type),
                    'description' => $slaEvent->details ?? "SLA marked as {$slaEvent->event_type}",
                    'timestamp' => Carbon::parse($slaEvent->event_at)->toIso8601String(),
                    'actor' => 'SLA Engine',
                ];
            }
        }

        // Sort chronologically ascending
        usort($timeline, fn ($a, $b) => strcmp($a['timestamp'], $b['timestamp']));

        return $timeline;
    }

    /**
     * Add comment to case (public or internal note).
     */
    public function addComment(
        HrServiceRequest $request,
        string $message,
        string $type = 'public',
        ?User $user = null,
        ?Employee $employee = null
    ): HrServiceRequestComment {
        $commentType = in_array(strtolower($type), ['internal', 'private'])
            ? ServiceCommentType::INTERNAL->value
            : ServiceCommentType::PUBLIC->value;

        $comment = $request->comments()->create([
            'tenant_id' => $request->tenant_id,
            'user_id' => $user?->id,
            'employee_id' => $employee?->id,
            'comment_type' => $commentType,
            'message' => $message,
        ]);

        // If agent added first public response, mark first response in SLA
        if ($user && $commentType === ServiceCommentType::PUBLIC->value) {
            $this->slaService->recordFirstResponse($request);
        }

        return $comment;
    }

    /**
     * Resolve Case with Resolution Notes.
     */
    public function resolveCase(HrServiceRequest $request, ?User $actor = null, ?string $resolutionNotes = null): HrServiceRequest
    {
        if ($resolutionNotes) {
            $this->addComment($request, "Resolution Note: {$resolutionNotes}", 'public', $actor);
        }

        return $this->requestService->transitionStatus(
            $request,
            ServiceRequestStatus::RESOLVED->value,
            $actor,
            $resolutionNotes ?? 'Case resolved by HR Shared Services.'
        );
    }

    /**
     * AI Case Summarization (Grounding & Safety Guardrails).
     */
    public function generateAiCaseSummary(HrServiceRequest $request): array
    {
        $timeline = $this->getCaseTimeline($request, true);
        $emp = $request->employee;
        $svc = $request->service;
        $sla = $request->slaInstance;

        $issue = $request->description ?? $request->subject;
        $missingInfo = [];
        if ($request->status === ServiceRequestStatus::WAITING_FOR_EMPLOYEE->value) {
            $missingInfo[] = 'Pending additional verification documents or confirmation from employee.';
        }

        $nextRecommendedStep = match ($request->status) {
            ServiceRequestStatus::DRAFT->value, ServiceRequestStatus::SUBMITTED->value => 'Assign to specialist queue and begin processing.',
            ServiceRequestStatus::ASSIGNED->value => 'Agent to review employee details and send initial response.',
            ServiceRequestStatus::WAITING_FOR_EMPLOYEE->value => 'Follow up with employee if no response within 24 hours.',
            ServiceRequestStatus::WAITING_FOR_APPROVAL->value => 'Await line manager authorization in Manager Workbench.',
            ServiceRequestStatus::RESOLVED->value => 'Collect employee CSAT feedback and archive record.',
            default => 'Review dynamic form inputs and complete fulfillment steps.',
        };

        return [
            'is_advisory_only' => true,
            'case_number' => $request->request_number,
            'summary' => [
                'issue' => $issue,
                'employee_request' => "Employee {$emp?->fullName()} ({$emp?->employee_code}) submitted request for {$svc?->name}.",
                'current_status' => strtoupper($request->status),
                'priority' => strtoupper($request->priority),
                'sla' => [
                    'status' => strtoupper($sla?->status ?? 'RUNNING'),
                    'due_at' => $request->due_at?->toIso8601String(),
                    'is_breached' => $request->due_at && $request->due_at->isPast(),
                ],
                'actions_taken' => count($timeline) . ' logged events in activity audit trail.',
                'missing_information' => empty($missingInfo) ? 'None identified.' : implode(' ', $missingInfo),
                'next_recommended_step' => $nextRecommendedStep,
            ],
            'citations' => [
                'Source: SmartHCM Service Delivery Master',
                'Source: Employee Context Master #' . ($emp?->employee_code ?? 'EMP'),
                'SLA Policy: ' . ($svc?->slaPolicy?->name ?? 'Standard Enterprise SLA'),
            ],
        ];
    }
}
