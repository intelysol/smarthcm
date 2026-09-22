# EPIC 2.60 — API Specification
## Unified Employee & Manager Experience Layer

All endpoints require authentication (`auth:sanctum` or standard session authentication) and are tenant-isolated via `X-Tenant-ID` header or session.

---

## 1. Employee Endpoints (`/api/me/*`)

### 1.1 Dashboard & Home
- `GET /api/me/dashboard`
  - Returns aggregated home payload:
    * `employee`: Profile basics (name, designation, department, photo).
    * `attendance`: Today's status (`NOT_CLOCKED_IN`, `CLOCKED_IN`, `BREAK`, `CLOCKED_OUT`), clock-in time, worked hours.
    * `schedule`: Today's shift (name, start, end, location).
    * `leave_balance`: Available, used, pending days across leave types.
    * `tasks_summary`: Pending tasks count and top 3 high priority tasks.
    * `requests_summary`: Open requests count and top 3 active requests.
    * `latest_payslip`: Period, net pay, publication date (masked summary).
    * `announcements`: Active organization announcements with acknowledgement status.
    * `quick_actions`: Authorized action pills.

### 1.2 Tasks & Requests
- `GET /api/me/tasks`
  - Query params: `status` (`pending`, `completed`), `category` (`compliance`, `learning`, `performance`, `timesheet`).
  - Returns normalized array of task objects: `[id, title, due_date, priority, source_module, status, action_url]`.
- `GET /api/me/requests`
  - Query params: `status` (`all`, `pending`, `approved`, `rejected`), `type` (`leave`, `expense`, `service`, `profile_change`).
  - Returns normalized array of request objects: `[id, type, type_label, title, status, submitted_at, current_step, next_action, approver, timeline]`.
- `GET /api/me/requests/{type}/{id}`
  - Returns detailed timeline, comments, approver notes, and attachments for specific request.

### 1.3 Profile & Identity
- `GET /api/me/profile`
  - Returns full multi-tab profile: Personal, Employment, Organization, Emergency Contacts, Dependents, Bank, Documents, Skills.
- `POST /api/me/profile/change-requests`
  - Submits controlled field modification (e.g. Bank details, emergency contact, legal name) into approval workflow.

### 1.4 Work, Attendance & Leave
- `GET /api/me/attendance`
  - Returns current week/month attendance sessions, timesheet summary, overtime hours, exceptions.
- `POST /api/me/attendance/clock`
  - Payload: `action` (`in`, `out`, `break_start`, `break_end`), optional `location` coordinates / note.
- `GET /api/me/leave`
  - Returns detailed balances per leave type, upcoming approved leaves, and leave history.
- `POST /api/me/leave/apply`
  - Payload: `leave_type_id`, `start_date`, `end_date`, `reason`, `attachments`. Submits directly to `leave_applications`.

### 1.5 Payroll & Documents
- `GET /api/me/pay`
  - Returns list of historical payslips for the authenticated employee.
- `GET /api/me/pay/payslips/{id}`
  - Securely streams or generates authorized payslip breakdown. Emits audit log.
- `GET /api/me/documents`
  - Returns categorized employee documents and unacknowledged company policies.
- `POST /api/me/documents/{id}/acknowledge`
  - Records policy acknowledgement timestamp and audit signature.

### 1.6 HR Services & AI Concierge
- `GET /api/me/services`
  - Returns available service catalog items with SLAs.
- `POST /api/me/services/requests`
  - Submits HR helpdesk ticket with category, priority, and description.
- `POST /api/me/ai/chat`
  - Conversational interaction with the Employee AI Concierge under authenticated employee context.

---

## 2. Manager Endpoints (`/api/manager/*`)

### 2.1 Workbench & Team Roster
- `GET /api/manager/dashboard`
  - Returns manager cockpit data: team count, present today, absent today, on leave today, pending approvals count, high-priority team alerts.
- `GET /api/manager/team`
  - Returns array of direct reports with current status, today's schedule, attendance state, and contact info.
- `GET /api/manager/capacity`
  - Returns team capacity summary: required hours, scheduled hours, absence impact, utilization rate.

### 2.2 Approval Inbox
- `GET /api/manager/approvals`
  - Returns aggregated pending approvals across all domain workflows (Leave, Expense, Timesheet, Profile changes, Services).
- `POST /api/manager/approvals/{type}/{id}/action`
  - Payload: `action` (`approve`, `reject`, `request_info`), `comments`.
  - Dispatches decision to underlying domain engine and notifies employee.

### 2.3 Team Alerts
- `GET /api/manager/alerts`
  - Returns actionable alerts: unexplained absences, pending SLA breaches, overdue training, overdue performance evaluations.
