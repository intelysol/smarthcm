<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Payroll\Models\PayrollPeriod;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrAnnouncement;
use App\Domains\SelfService\Models\HrServiceGeneratedDocument;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\PortalNotification;
use App\Models\User;

class EmployeePortalService
{
    public function __construct(
        protected AnnouncementService $announcementService,
        protected ServiceCatalogService $catalogService
    ) {}

    public function getEmployeeDashboard(Employee $employee): array
    {
        $tenantId = $employee->tenant_id;

        $openRequestsCount = HrServiceRequest::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', [ServiceRequestStatus::RESOLVED->value, ServiceRequestStatus::CLOSED->value, ServiceRequestStatus::CANCELLED->value])
            ->count();

        $recentRequests = HrServiceRequest::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->with(['service.category', 'assignedQueue', 'slaInstance'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $announcements = $this->announcementService->getVisibleAnnouncementsForEmployee($employee)->take(4);

        $recentDocuments = HrServiceGeneratedDocument::where('tenant_id', $tenantId)
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $popularServices = $this->catalogService->getPopularServices($tenantId, 6);

        return [
            'employee' => $employee->load(['department', 'designation', 'branch', 'company']),
            'open_requests_count' => $openRequestsCount,
            'recent_requests' => $recentRequests,
            'announcements' => $announcements,
            'recent_documents' => $recentDocuments,
            'popular_services' => $popularServices,
            'quick_links' => [
                ['name' => 'Submit HR Request', 'route' => 'self-service.catalog.index', 'icon' => 'fa-ticket'],
                ['name' => 'View My Requests', 'route' => 'self-service.requests.index', 'icon' => 'fa-list-check'],
                ['name' => 'Company Knowledge Base', 'route' => 'self-service.knowledge.index', 'icon' => 'fa-book'],
                ['name' => 'Announcements', 'route' => 'self-service.announcements.index', 'icon' => 'fa-bullhorn'],
            ],
        ];
    }
}
