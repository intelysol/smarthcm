<?php

namespace App\Domains\EmployeeExperience\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface;
use App\Domains\EmployeeExperience\Services\EmployeeDashboardService;
use App\Domains\EmployeeExperience\Services\EmployeeExperienceAuditService;
use App\Domains\EmployeeExperience\Services\EmployeeQuickActionService;
use App\Domains\EmployeeExperience\Services\EmployeeRequestService;
use App\Domains\EmployeeExperience\Services\EmployeeTaskService;
use App\Domains\EmployeeProfile\Services\EmployeeDirectoryService;
use App\Domains\EmployeeProfile\Services\EmployeeProfileService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeeExperienceApiController extends Controller
{
    public function __construct(
        protected EmployeeDashboardService $dashboardService,
        protected EmployeeTaskService $taskService,
        protected EmployeeRequestService $requestService,
        protected EmployeeQuickActionService $quickActionService,
        protected EmployeeExperienceAuditService $auditService,
        protected ?EmployeeAiConciergeInterface $aiConcierge = null
    ) {}

    protected function resolveEmployee(Request $request): Employee
    {
        $user = $request->user();
        $tenantId = $request->header('X-Tenant-ID') ?? $user?->tenant_id;

        $employeeId = $request->header('X-Employee-ID');
        if ($employeeId && $tenantId) {
            $emp = Employee::where('tenant_id', $tenantId)->where('id', $employeeId)->first();
            if ($emp) {
                return $emp;
            }
        }

        if ($user) {
            if ($user->employee) {
                return $user->employee;
            }
            $emp = Employee::where('user_id', $user->id)->first();
            if ($emp) {
                return $emp;
            }
        }

        abort(401, 'No authenticated employee profile found.');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $dashboard = $this->dashboardService->getDashboard($employee);

        return response()->json($dashboard);
    }

    public function tasks(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tasks = $this->taskService->getPendingTasks($employee);

        return response()->json(['tasks' => $tasks]);
    }

    public function requests(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $status = $request->query('status');
        $requests = $this->requestService->getUnifiedRequests($employee, $status);

        return response()->json(['requests' => $requests]);
    }

    public function quickActions(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $actions = $this->quickActionService->getQuickActionsForEmployee($employee);

        return response()->json(['quick_actions' => $actions]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;
        $today = Carbon::today()->toDateString();

        $todaySession = null;
        $history = [];

        if (Schema::hasTable('attendance_sessions')) {
            $todaySession = DB::table('attendance_sessions')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->whereDate('session_date', $today)
                ->first();

            $history = DB::table('attendance_sessions')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->orderBy('session_date', 'desc')
                ->limit(14)
                ->get();
        }

        return response()->json([
            'today' => $todaySession,
            'status' => $todaySession ? (empty($todaySession->clock_out) ? 'CLOCKED_IN' : 'CLOCKED_OUT') : 'NOT_CLOCKED_IN',
            'history' => $history,
        ]);
    }

    public function clock(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $action = $request->input('action', 'toggle');
        $notes = $request->input('notes');

        $result = $this->dashboardService->clockToggle($employee, $action, $notes);

        return response()->json($result);
    }

    public function leave(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;
        $year = (int) Carbon::now()->year;

        $balances = [];
        if (Schema::hasTable('leave_balances')) {
            $query = DB::table('leave_balances')
                ->where('leave_balances.tenant_id', $tenantId)
                ->where('leave_balances.employee_id', $employee->id)
                ->where('leave_balances.year', $year);

            if (Schema::hasTable('leave_types')) {
                $query->leftJoin('leave_types', 'leave_balances.leave_type_id', '=', 'leave_types.id')
                      ->select('leave_balances.*', 'leave_types.name as leave_type_name', 'leave_types.code as leave_type_code');
            }

            $balances = $query->get();
        }

        $applications = [];
        if (Schema::hasTable('leave_applications')) {
            $applications = DB::table('leave_applications')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }

        return response()->json([
            'year' => $year,
            'balances' => $balances,
            'applications' => $applications,
        ]);
    }

    public function applyLeave(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);

        $request->validate([
            'leave_type_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'duration' => 'required|numeric|min:0.5',
        ]);

        $result = $this->requestService->submitLeaveApplication(
            $employee,
            $request->input('leave_type_id'),
            $request->input('start_date'),
            $request->input('end_date'),
            (float) $request->input('duration'),
            $request->input('reason')
        );

        return response()->json($result, 201);
    }

    public function profile(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $user = $request->user();

        return response()->json([
            'personal' => [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'full_name' => $employee->fullName(),
                'employee_code' => $employee->employee_code ?? $employee->employee_number,
                'gender' => $employee->gender,
                'date_of_birth' => $employee->date_of_birth?->toDateString(),
                'nationality' => $employee->nationality,
                'marital_status' => $employee->marital_status,
                'personal_email' => $employee->personal_email,
                'mobile' => $employee->mobile,
                'present_address' => $employee->present_address,
                'photo_path' => $employee->photo_path,
            ],
            'employment' => [
                'status' => $employee->employment_status,
                'joining_date' => $employee->joining_date?->toDateString(),
                'department' => $employee->department?->name ?? 'General',
                'designation' => $employee->designation?->name ?? 'Specialist',
                'work_location' => $employee->workLocation?->name ?? 'Headquarters',
                'reporting_manager' => $employee->reportingManager ? [
                    'id' => $employee->reportingManager->id,
                    'name' => $employee->reportingManager->fullName(),
                    'code' => $employee->reportingManager->employee_code,
                ] : null,
            ],
            'emergency_contacts' => [
                'phone' => $employee->emergency_phone,
            ],
        ]);
    }

    public function pay(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        $payslips = [];
        if (Schema::hasTable('payroll_payslips')) {
            $payslips = DB::table('payroll_payslips')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'employee' => $employee->fullName(),
            'payslips' => $payslips,
        ]);
    }

    public function payslipDetail(Request $request, string $id): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        if (!Schema::hasTable('payroll_payslips')) {
            abort(404, 'Payroll payslips table not found.');
        }

        $payslip = DB::table('payroll_payslips')
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        if (!$payslip) {
            abort(404, 'Payslip not found.');
        }

        // Strict Horizontal Isolation: Employee A cannot access Employee B's payslip
        if ($payslip->employee_id !== $employee->id) {
            abort(403, 'Unauthorized access to payslip.');
        }

        $this->auditService->log(
            $tenantId,
            $employee->id,
            'PAYSLIP_VIEW',
            $request->user()?->id,
            'payroll_payslips',
            $id
        );

        return response()->json([
            'payslip' => $payslip,
        ]);
    }

    public function documents(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;

        $docs = [];
        if (Schema::hasTable('employee_documents')) {
            $docs = DB::table('employee_documents')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $policies = [];
        if (Schema::hasTable('employee_document_acknowledgements')) {
            $policies = DB::table('employee_document_acknowledgements')
                ->where('tenant_id', $tenantId)
                ->where('employee_id', $employee->id)
                ->get();
        }

        return response()->json([
            'documents' => $docs,
            'policy_acknowledgements' => $policies,
        ]);
    }

    public function acknowledgeDocument(Request $request, string $id): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;
        $now = Carbon::now();

        // 1. If ID matches an uncompleted requirement, complete it and create acknowledgement
        if (Schema::hasTable('employee_document_requirements')) {
            $req = DB::table('employee_document_requirements')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();

            if ($req) {
                DB::table('employee_document_requirements')
                    ->where('id', $id)
                    ->update([
                        'status' => 'submitted',
                        'updated_at' => $now,
                    ]);

                if (Schema::hasTable('employee_document_acknowledgements') && $req->employee_document_id) {
                    DB::table('employee_document_acknowledgements')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'employee_document_id' => $req->employee_document_id,
                        'employee_id' => $employee->id,
                        'document_version' => 1,
                        'acknowledged_at' => $now,
                        'ip_address' => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 255),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        // 2. If ID matches an existing acknowledgement record, update or ensure acknowledged_at is recorded
        if (Schema::hasTable('employee_document_acknowledgements')) {
            $ack = DB::table('employee_document_acknowledgements')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();

            if ($ack) {
                DB::table('employee_document_acknowledgements')
                    ->where('id', $id)
                    ->update([
                        'acknowledged_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        }

        if (!$req && !$ack) {
            abort(404, 'Document requirement not found for this employee.');
        }

        $this->auditService->log(
            $tenantId,
            $employee->id,
            'POLICY_ACKNOWLEDGED',
            $request->user()?->id,
            'employee_document_acknowledgements',
            $id
        );

        return response()->json(['success' => true, 'message' => 'Document acknowledged.']);
    }

    public function directory(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;
        $search = $request->query('q');

        $query = Employee::where('tenant_id', $tenantId)
            ->where('employment_status', 'active')
            ->with(['department', 'designation', 'workLocation']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        $people = $query->limit(30)->get()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->fullName(),
            'code' => $p->employee_code ?? $p->employee_number,
            'department' => $p->department?->name ?? 'General',
            'designation' => $p->designation?->name ?? 'Specialist',
            'email' => $p->official_email ?? $p->personal_email,
            'photo_path' => $p->photo_path,
        ]);

        return response()->json(['people' => $people]);
    }

    public function aiChat(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);
        $tenantId = $employee->tenant_id;
        $userId = $request->user()?->id ?? 'system';
        $prompt = $request->input('prompt', '');

        if ($this->aiConcierge) {
            $session = $this->aiConcierge->startSession($tenantId, $userId, $employee->id, 'EMPLOYEE');
            $result = $this->aiConcierge->chat((string) $session->id, $prompt, $tenantId, $userId, $employee->id);

            $this->auditService->log(
                $tenantId,
                $employee->id,
                'AI_CONCIERGE_CHAT',
                $userId,
                null,
                null,
                ['prompt' => $prompt]
            );

            return response()->json([
                'response' => $result['message']['content'] ?? ($result['response'] ?? 'I have processed your request.'),
                'message' => $result['message'] ?? null,
                'action_proposal' => $result['action_proposal'] ?? null,
                'citations' => $result['citations'] ?? [],
                'employee_context' => [
                    'employee_id' => $employee->id,
                    'name' => $employee->fullName(),
                ],
            ]);
        }

        // Lightweight grounded fallback answering standard questions
        $answer = "I am your HCM HR Concierge for {$employee->fullName()}. You can ask about your schedule, leave balances, or company policies.";
        $lower = strtolower($prompt);
        if (str_contains($lower, 'leave')) {
            $dash = $this->dashboardService->getDashboard($employee);
            $rem = $dash['leave_summary']['remaining_days'] ?? 0;
            $answer = "You currently have {$rem} days of remaining leave available for this year.";
        } elseif (str_contains($lower, 'shift') || str_contains($lower, 'schedule')) {
            $dash = $this->dashboardService->getDashboard($employee);
            $s = $dash['schedule'];
            $answer = "Your shift today is '{$s['shift_name']}' scheduled from {$s['start_time']} to {$s['end_time']} at {$s['location']}.";
        } elseif (str_contains($lower, 'payslip') || str_contains($lower, 'pay')) {
            $dash = $this->dashboardService->getDashboard($employee);
            $p = $dash['latest_payslip'];
            $answer = $p ? "Your latest payslip for {$p['period']} with net pay of {$p['currency']} {$p['net_pay']} is available in My Pay." : "Your latest payslip can be accessed securely under My Pay.";
        }

        $this->auditService->log(
            $tenantId,
            $employee->id,
            'AI_CONCIERGE_CHAT',
            $userId,
            null,
            null,
            ['prompt' => $prompt]
        );

        return response()->json([
            'response' => $answer,
            'citations' => ['Source: HCM Employee Master & Balances'],
            'employee_context' => [
                'employee_id' => $employee->id,
                'name' => $employee->fullName(),
            ],
        ]);
    }
}
