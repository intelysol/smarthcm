<?php

namespace App\Domains\EmployeeDocuments\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentRequest;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentAnalyticsService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDocumentDashboardController extends Controller
{
    public function __construct(
        protected EmployeeDocumentAnalyticsService $analyticsService,
        protected EmployeeDocumentService $docService
    ) {
    }

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id ?? '00000000-0000-0000-0000-000000000000';

        $metrics = $this->analyticsService->getMetrics($tenantId);

        $pendingVerifications = EmployeeDocument::where('tenant_id', $tenantId)
            ->where('verification_status', 'pending')
            ->with(['employee.department', 'documentType.category'])
            ->latest()
            ->take(5)
            ->get();

        $expiringSoon = EmployeeDocument::where('tenant_id', $tenantId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->with(['employee.department', 'documentType'])
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        $recentRequests = EmployeeDocumentRequest::where('tenant_id', $tenantId)
            ->with(['employee', 'documentType', 'requester'])
            ->latest()
            ->take(5)
            ->get();

        return view('employee_documents.index', compact('metrics', 'pendingVerifications', 'expiringSoon', 'recentRequests'));
    }

    public function personnelFile(Request $request, string $employeeId): View
    {
        $employee = Employee::with('department')->findOrFail($employeeId);
        $fileData = $this->docService->getPersonnelFile($employee, $request->user());

        return view('employee_documents.personnel_file', compact('employee', 'fileData'));
    }
}
