<?php

namespace App\Domains\EmployeeExperience\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeExperience\Services\EmployeeDashboardService;
use App\Domains\EmployeeExperience\Services\EmployeeQuickActionService;
use App\Domains\EmployeeExperience\Services\EmployeeRequestService;
use App\Domains\EmployeeExperience\Services\EmployeeTaskService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EmployeeExperienceWebController extends Controller
{
    public function __construct(
        protected EmployeeDashboardService $dashboardService,
        protected EmployeeTaskService $taskService,
        protected EmployeeRequestService $requestService,
        protected EmployeeQuickActionService $quickActionService
    ) {}

    protected function resolveEmployee(Request $request): Employee
    {
        $user = $request->user();
        $tenantId = $request->query('tenant_id') ?? $request->header('X-Tenant-ID') ?? $user?->tenant_id;
        $employeeId = $request->query('employee_id') ?? $request->header('X-Employee-ID');

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

        // Development/fallback: return first employee in tenant or system
        $emp = Employee::query();
        if ($tenantId) {
            $emp->where('tenant_id', $tenantId);
        }
        $first = $emp->first();
        if ($first) {
            return $first;
        }

        abort(404, 'No employee record found for portal.');
    }

    public function dashboard(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $dashboard = $this->dashboardService->getDashboard($employee);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        return view('portal.employee.dashboard', compact('employee', 'dashboard', 'isManager'));
    }

    public function work(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $dashboard = $this->dashboardService->getDashboard($employee);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        $history = [];
        if (Schema::hasTable('attendance_sessions')) {
            $history = DB::table('attendance_sessions')
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->orderBy('session_date', 'desc')
                ->limit(10)
                ->get();
        }

        return view('portal.employee.work', compact('employee', 'dashboard', 'history', 'isManager'));
    }

    public function requests(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $requests = $this->requestService->getUnifiedRequests($employee, $request->query('status'));
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        $leaveTypes = [];
        if (Schema::hasTable('leave_types')) {
            $leaveTypes = DB::table('leave_types')->where('tenant_id', $employee->tenant_id)->get();
        }

        return view('portal.employee.requests', compact('employee', 'requests', 'leaveTypes', 'isManager'));
    }

    public function profile(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        return view('portal.employee.profile', compact('employee', 'isManager'));
    }

    public function pay(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        $payslips = [];
        if (Schema::hasTable('payroll_payslips')) {
            $payslips = DB::table('payroll_payslips')
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('portal.employee.pay', compact('employee', 'payslips', 'isManager'));
    }

    public function growth(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        $courses = [];
        if (Schema::hasTable('course_enrollments')) {
            $courses = DB::table('course_enrollments')
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->get();
        }

        $reviews = [];
        if (Schema::hasTable('performance_reviews')) {
            $reviews = DB::table('performance_reviews')
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->get();
        }

        return view('portal.employee.growth', compact('employee', 'courses', 'reviews', 'isManager'));
    }

    public function documents(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        $documents = [];
        if (Schema::hasTable('employee_documents')) {
            $documents = DB::table('employee_documents')
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->get();
        }

        $acknowledgements = [];
        if (Schema::hasTable('employee_document_acknowledgements')) {
            $acknowledgements = DB::table('employee_document_acknowledgements')
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->get();
        }

        return view('portal.employee.documents', compact('employee', 'documents', 'acknowledgements', 'isManager'));
    }

    public function services(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        $services = [];
        if (Schema::hasTable('hr_services')) {
            $services = DB::table('hr_services')
                ->where('tenant_id', $employee->tenant_id)
                ->get();
        }

        $myRequests = [];
        if (Schema::hasTable('hr_service_requests')) {
            $myRequests = DB::table('hr_service_requests')
                ->where('tenant_id', $employee->tenant_id)
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('portal.employee.services', compact('employee', 'services', 'myRequests', 'isManager'));
    }

    public function directory(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();

        $people = Employee::where('tenant_id', $employee->tenant_id)
            ->where('employment_status', 'active')
            ->with(['department', 'designation'])
            ->limit(30)
            ->get();

        return view('portal.employee.directory', compact('employee', 'people', 'isManager'));
    }

    public function privacy(Request $request): View
    {
        $employee = $this->resolveEmployee($request);
        $isManager = Employee::where('reporting_manager_id', $employee->id)->exists();
        $user = $request->user();

        $privacyRequests = [];
        if ($user) {
            $privacyRequests = \App\Domains\Compliance\Models\PrivacyRequest::where('tenant_id', $employee->tenant_id)
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        $processingActivities = \App\Domains\Compliance\Models\PrivacyProcessingActivity::where('tenant_id', $employee->tenant_id)->get();

        return view('portal.employee.privacy', compact('employee', 'privacyRequests', 'processingActivities', 'isManager'));
    }
}
