<?php

namespace App\Domains\EmployeeProfile\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeProfile\Services\EmployeeDirectoryService;
use App\Domains\EmployeeProfile\Services\EmployeeProfileService;
use App\Domains\EmployeeProfile\Services\OrgChartService;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDirectoryDashboardController extends Controller
{
    public function __construct(
        protected EmployeeDirectoryService $directoryService,
        protected OrgChartService $orgChartService,
        protected EmployeeProfileService $profileService
    ) {
    }

    public function directoryView(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $filters = $request->only(['search', 'department_id', 'branch_id', 'status']);
        $employees = $this->directoryService->searchDirectory($request->user(), $filters, 12);

        $departments = Department::where('tenant_id', $tenantId)->orderBy('name')->get();
        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('employee_profile.directory', compact('employees', 'departments', 'branches', 'filters'));
    }

    public function orgChartView(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        $rootNodes = $this->orgChartService->getRootNodes($tenantId);

        return view('employee_profile.org_chart', compact('rootNodes'));
    }

    public function profileView(Request $request, string $id): View
    {
        $employee = Employee::with(['department', 'designation', 'branch', 'workLocation', 'reportingManager', 'employmentType'])->findOrFail($id);
        $summary = $this->profileService->getProfileSummary($employee, $request->user());

        return view('employee_profile.profile', compact('employee', 'summary'));
    }
}
