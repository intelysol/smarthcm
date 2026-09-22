# EPIC 2.60 — Testing & Verification Strategy
## HCM Employee & Manager Experience Layer

---

## 1. Automated Test Suites

### 1.1 Employee Experience Tests (`tests/Feature/EmployeeExperience/EmployeeExperienceTest.php`)
1. **Aggregated Employee Dashboard**:
   - Verify `GET /api/me/dashboard` returns correct payload structure.
   - Verify today's attendance status, shift schedule, leave balance, and pending counts.
2. **Unified Tasks Aggregation**:
   - Verify tasks from multiple domains (Onboarding, Document Acknowledgement, Performance, Timesheets) appear in `GET /api/me/tasks`.
3. **Unified Requests Aggregation**:
   - Verify requests across Leave, Expenses, and HR Services appear in `GET /api/me/requests` with valid status and timeline steps.
4. **Leave Application & Balance Check**:
   - Verify `POST /api/me/leave/apply` creates record in `leave_applications` and links to workflow.
   - Verify leave balances deduct or reflect pending status.
5. **Attendance Clocking**:
   - Verify `POST /api/me/attendance/clock` starts an `AttendanceSession` with proper timestamp and status.
6. **Secure Payslip Access**:
   - Verify employee can view their own payslip.
   - Verify Employee A cannot access Employee B's payslip (HTTP 403 / 404).
7. **Policy Acknowledgement**:
   - Verify `POST /api/me/documents/{id}/acknowledge` marks document acknowledged and logs audit record.

### 1.2 Manager Workbench Tests (`tests/Feature/EmployeeExperience/ManagerWorkbenchTest.php`)
1. **Manager Cockpit & Team Roster**:
   - Verify `GET /api/manager/dashboard` and `GET /api/manager/team` only returns manager's direct reports.
   - Verify manager cannot see employees from other managers or other tenants.
2. **Unified Approval Inbox**:
   - Verify pending leave, expense, and service requests appear in `GET /api/manager/approvals`.
3. **Approval Action Execution**:
   - Verify `POST /api/manager/approvals/{type}/{id}/action` with `action=approve` updates the domain record status to approved.
   - Verify approval triggers employee notification.
4. **Team Alerts & Capacity**:
   - Verify alerts for absent employees or overdue tasks.
   - Verify team capacity metrics (scheduled hours, utilization).

### 1.3 Security & Multi-Tenant Tests
- Cross-tenant data isolation.
- Unauthorized client ID spoofing prevention.
- Role-based route access controls.

---

## 2. Test Execution Command
Run test suites via PHP 8.3 CLI:
```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test tests/Feature/EmployeeExperience/EmployeeExperienceTest.php
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test tests/Feature/EmployeeExperience/ManagerWorkbenchTest.php
```
Target: 100% passing tests with zero regressions.
