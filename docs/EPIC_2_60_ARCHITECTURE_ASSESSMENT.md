# EPIC 2.60 — Architecture Assessment: HCM Employee & Manager Experience, Unified Self-Service Portal & Daily Workbench

## 1. Executive Summary
Epic 2.60 creates the unified enterprise experience layer for employees and managers. Rather than exposing dozens of discrete technical modules, the system establishes an integrated Digital Workplace under `App\Domains\EmployeeExperience` and `App\Domains\SelfService`. This layer aggregates Core HR, Leave, Attendance, Scheduling, Payroll, Benefits, Documents, Learning, Performance, Expenses, HR Services, and AI Concierge into cohesive daily cockpits.

---

## 2. Core Architectural Principles

### 2.1 Experience Orchestration Layer Pattern
```text
┌────────────────────────────────────────────────────────────────────────┐
│                   UNIFIED DIGITAL WORKPLACE (EPIC 2.60)                │
│   Employee Portal  │  Manager Workbench  │  Unified Search & Concierge │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ Aggregation & Delegation
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                      DOMAIN SERVICES LAYER (EXISTING)                  │
│  Core HR  │  Leave  │ Attendance │ Payroll │ Documents │ Performance   │
│  Learning │ Expense │ HR Service │ Workflow│    AI     │   Analytics   │
└────────────────────────────────────────────────────────────────────────┘
```
- **Experience Layer**: Orchestrates, formats, and presents data; manages user preferences and session context.
- **Domain Layer**: Authoritative owner of business rules, entity models, database state mutations, and statutory constraints.
- **Rule of Zero Engine Duplication**: The experience layer never writes directly to transactional domain tables without going through domain services.

---

## 3. Repository Baseline Analysis

| Capability Required | Existing Repository Component | Reuse Strategy in Epic 2.60 |
|---|---|---|
| Employee Master & Hierarchy | `App\Domains\Employee\Models\Employee` | Direct model query; reportingManager relationship traversal |
| Profile & Directory | `App\Domains\EmployeeProfile\Services\EmployeeProfileService` | Consumed for profile tabs, sensitive data masking, directory search |
| Leave Balances & Applications | `leave_balances`, `leave_applications`, `App\Domains\Absence` | Consumed for balance cards, application submission, timeline tracking |
| Attendance & Punching | `App\Domains\Attendance\Models\AttendanceSession`, `ShiftDefinition` | Clock in/out triggers `AttendanceSession`, schedule shows today's shift |
| Payroll & Payslips | `App\Domains\Payroll\Models\PayrollPayslip`, `PayslipService` | Read-only payslip retrieval with strict employee ownership checks |
| Benefits Summary | `App\Domains\Benefits\Models\HcmBenefitPlan`, `BenefitEnrollment` | Enrollment status and coverage summary widgets |
| Documents & Signatures | `App\Domains\EmployeeDocuments\Models\EmployeeDocument` | Category view, policy acknowledgement tracking |
| Learning & Certifications | `App\Domains\Learning\Models\CourseEnrollment` | Assigned / overdue training tasks widget |
| Goals & Reviews | `App\Domains\Performance\Models\PerformanceReview`, `PerformanceGoal` | Active goals and review cycle tasks |
| Expense Claims | `App\Domains\Expenses\Models\ExpenseClaim` | Recent claims list, submit quick action |
| HR Service Catalog | `App\Domains\SelfService\Models\HrServiceRequest`, `ServiceCatalogService` | Helpdesk ticket intake and SLA status |
| Workflow & Approvals | `App\Domains\Workflow\Models\WorkflowInstance`, `WorkflowAssignment` | Manager approval aggregation and unified action dispatch |
| AI Concierge | `App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface` | Embedded chat widget with server-side authenticated context |
| Notifications & Announcements| `App\Domains\SelfService\Models\PortalNotification`, `HrAnnouncement` | Real-time notification center and priority broadcast banners |

---

## 4. Key Services Architecture
1. **`EmployeeDashboardService`**: Aggregates home widgets (Today's Schedule, Live Attendance Status, Leave Balance, Recent Tasks, Requests, Payslip summary, Announcements).
2. **`EmployeeTaskService`**: Aggregates pending action items across Onboarding, Policy Acknowledgements, Performance Reviews, Learning Courses, and Timesheets.
3. **`EmployeeRequestService`**: Aggregates employee requests across Leave, Expenses, Helpdesk, Profile Changes, and Document requests with unified timeline steps.
4. **`ManagerWorkbenchService`**: Aggregates team attendance, pending multi-domain approvals, team alerts, and capacity summaries for managers.
5. **`EmployeeQuickActionService`**: Governed quick actions based on tenant configuration and user entitlements.
6. **`EmployeeExperienceAuditService`**: Logs all sensitive employee self-service events (payslip access, document downloads, approvals).

---

## 5. Security & Isolation Model
1. **Tenant Isolation**: Every database interaction is scoped to `tenant_id`.
2. **Employee Isolation**: Normal employees can only query their own records.
3. **Manager Scope Enforcement**: Managers can only view and act on direct and indirect reports within their organization branch.
4. **Identity Grounding**: Authenticated `User` ID is deterministically resolved to `Employee` ID on the server side (`Auth::user()->employee`). Client parameters cannot override employee identity.
