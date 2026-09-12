<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Services\UnifiedServiceSearchAndAiService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrSharedServicesWorkspaceController extends Controller
{
    public function __construct(
        protected UnifiedServiceSearchAndAiService $aiService
    ) {}

    public function workspace(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? $request->header('X-Tenant-ID') ?? 'default';
        $user = $request->user();

        // 1. My Queue
        $myQueue = HrServiceRequest::where('tenant_id', $tenantId)
            ->where('assigned_user_id', $user?->id)
            ->whereNotIn('status', ['closed', 'cancelled', 'resolved'])
            ->with(['service', 'employee'])
            ->latest()
            ->paginate(15);

        // 2. Unassigned Queue
        $unassigned = HrServiceRequest::where('tenant_id', $tenantId)
            ->whereNull('assigned_user_id')
            ->whereNotIn('status', ['draft', 'closed', 'cancelled'])
            ->with(['service', 'employee'])
            ->latest()
            ->paginate(15);

        // 3. Overdue & SLA At Risk
        $overdue = HrServiceRequest::where('tenant_id', $tenantId)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['closed', 'cancelled', 'resolved'])
            ->with(['service', 'employee'])
            ->latest()
            ->paginate(15);

        // 4. Waiting for Employee
        $waitingForEmployee = HrServiceRequest::where('tenant_id', $tenantId)
            ->where('status', ServiceRequestStatus::WAITING_FOR_EMPLOYEE->value)
            ->with(['service', 'employee'])
            ->latest()
            ->paginate(15);

        // 5. Queues Summary
        $queues = HrServiceQueue::where('tenant_id', $tenantId)
            ->withCount(['requests' => fn ($q) => $q->whereNotIn('status', ['closed', 'cancelled', 'resolved'])])
            ->get();

        return response()->json([
            'my_queue' => $myQueue,
            'unassigned' => $unassigned,
            'overdue' => $overdue,
            'waiting_for_employee' => $waitingForEmployee,
            'queues' => $queues,
            'counts' => [
                'my_queue_count' => $myQueue->total(),
                'unassigned_count' => $unassigned->total(),
                'overdue_count' => $overdue->total(),
                'waiting_for_employee_count' => $waitingForEmployee->total(),
            ],
        ]);
    }

    public function employeeContext(Employee $employee): JsonResponse
    {
        $tenantId = $employee->tenant_id;

        $recentRequests = HrServiceRequest::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->with('service')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'name' => "{$employee->first_name} {$employee->last_name}",
                'email' => $employee->official_email ?? $employee->email,
                'employment_status' => $employee->employment_status,
                'joining_date' => $employee->joining_date?->format('Y-m-d'),
                'department' => $employee->department?->name,
                'designation' => $employee->designation?->name,
                'manager' => $employee->reportingManager ? "{$employee->reportingManager->first_name} {$employee->reportingManager->last_name}" : null,
            ],
            'recent_requests' => $recentRequests,
            'service_history' => [
                'total_requests' => HrServiceRequest::where('tenant_id', $tenantId)->where('employee_id', $employee->id)->count(),
                'resolved_count' => HrServiceRequest::where('tenant_id', $tenantId)->where('employee_id', $employee->id)->where('status', 'resolved')->count(),
            ],
        ]);
    }

    public function aiAdvisory(HrServiceRequest $request): JsonResponse
    {
        $advisory = $this->aiService->generateAgentAdvisory($request);
        return response()->json($advisory);
    }
}
