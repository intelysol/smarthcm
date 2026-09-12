<?php

namespace App\Domains\PersonalData\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeBankChangeRequest;
use App\Domains\PersonalData\Models\HcmEmployeeDataChangeRequest;
use App\Domains\PersonalData\Services\EmployeeDataQualityService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PersonalDataDashboardController extends Controller
{
    public function __construct(
        protected EmployeeDataQualityService $qualityService
    ) {}

    /**
     * Master Data Governance Dashboard view.
     */
    public function governanceDashboard(Request $request): View
    {
        $tenantId = $request->user()->tenant_id ?? (Employee::first()?->tenant_id ?? '');

        $qualitySummary = $this->qualityService->getTenantQualitySummary($tenantId);
        $pendingBankRequests = HcmEmployeeBankChangeRequest::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
        $pendingDataChanges = HcmEmployeeDataChangeRequest::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();

        return view('personal_data.governance_dashboard', compact('qualitySummary', 'pendingBankRequests', 'pendingDataChanges'));
    }

    /**
     * Employee Personal Data Portal view.
     */
    public function employeePortal(Request $request, string $employeeId): View
    {
        $employee = Employee::findOrFail($employeeId);

        return view('personal_data.portal', compact('employee'));
    }

    /**
     * Change Requests Management view.
     */
    public function changeRequests(Request $request): View
    {
        $tenantId = $request->user()->tenant_id ?? (Employee::first()?->tenant_id ?? '');

        return view('personal_data.change_requests', compact('tenantId'));
    }
}
