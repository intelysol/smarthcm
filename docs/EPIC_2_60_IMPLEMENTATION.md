# EPIC 2.60 — Implementation Specification
## HCM Employee & Manager Experience, Unified Self-Service Portal & Daily Workbench

---

## 1. Directory Structure & Namespace
All new experience orchestration components will reside under:
`App\Domains\EmployeeExperience\` and extend `App\Domains\SelfService\`.

### Models
- `App\Domains\EmployeeExperience\Models\HcmExperiencePreference`: User dashboard widget preferences and quick action shortcuts.
- `App\Domains\EmployeeExperience\Models\HcmExperienceAudit`: Audit trail for sensitive self-service accesses (payslip downloads, document views, approval decisions).

### Services
- `App\Domains\EmployeeExperience\Services\EmployeeDashboardService`: Consolidated employee dashboard aggregation.
- `App\Domains\EmployeeExperience\Services\EmployeeTaskService`: Unified task collector across Onboarding, Documents, Performance, Learning, and Timesheets.
- `App\Domains\EmployeeExperience\Services\EmployeeRequestService`: Unified request collector and normalizer (Leave, Expenses, Services, Profile Changes, Documents).
- `App\Domains\EmployeeExperience\Services\EmployeeQuickActionService`: Configurable quick action provider.
- `App\Domains\EmployeeExperience\Services\ManagerWorkbenchService`: Manager cockpit, team roster, approvals inbox, alerts, and capacity calculation.
- `App\Domains\EmployeeExperience\Services\EmployeeExperienceAuditService`: Audit logging engine.

### Controllers & Routes
- `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceApiController`: Full REST API (`/api/me/*`).
- `App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkbenchApiController`: Manager REST API (`/api/manager/*`).
- `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController`: Blade view controller (`/portal/*`).
- `App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkbenchWebController`: Manager Blade view controller (`/portal/manager/*`).

---

## 2. Blade Views
- `resources/views/portal/layout.blade.php`: Shared base layout with header, navigation, search, and AI concierge drawer.
- `resources/views/portal/employee/dashboard.blade.php`: Unified Employee Home.
- `resources/views/portal/employee/work.blade.php`: My Work (Schedule, Live Attendance, Timesheet).
- `resources/views/portal/employee/requests.blade.php`: My Requests (Unified request tracker & new request modal).
- `resources/views/portal/employee/profile.blade.php`: My Information (Multi-tab profile & change request modal).
- `resources/views/portal/employee/pay.blade.php`: My Pay (Payslip history & breakdown).
- `resources/views/portal/employee/growth.blade.php`: My Growth (Goals, Reviews, Courses).
- `resources/views/portal/employee/documents.blade.php`: My Documents (Categories, download, policy acknowledgement).
- `resources/views/portal/employee/services.blade.php`: HR Services (Service catalog & ticket tracker).
- `resources/views/portal/employee/directory.blade.php`: People Directory & Org Chart.
- `resources/views/portal/manager/workbench.blade.php`: Manager Cockpit & Team Attendance.
- `resources/views/portal/manager/approvals.blade.php`: Unified Approval Inbox.
