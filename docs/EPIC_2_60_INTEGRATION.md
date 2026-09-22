# EPIC 2.60 — Domain Integration Architecture
## HCM Employee & Manager Experience Layer

---

## 1. Domain Integration Map

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│                    EMPLOYEE & MANAGER EXPERIENCE LAYER                      │
│                  (App\Domains\EmployeeExperience & SelfService)             │
└──────┬──────────┬──────────┬──────────┬──────────┬──────────┬────────┬──────┘
       │          │          │          │          │          │        │
       ▼          ▼          ▼          ▼          ▼          ▼        ▼
 ┌──────────┐┌──────────┐┌──────────┐┌──────────┐┌──────────┐┌──────┐┌─────────┐
 │ Core HR  ││  Leave   ││Attendance││ Payroll  ││Documents ││Perf/ ││   AI    │
 │ (Profile)││ (Absence)││(Sessions)││(Payslips)││ (Acknow) ││Learn ││Concierg│
 └──────────┘└──────────┘└──────────┘└──────────┘└──────────┘└──────┘└─────────┘
```

### 1. Core HR & Profile (`App\Domains\Employee`, `App\Domains\EmployeeProfile`)
- **Integration**: Queries `Employee` model for basic personal and employment data. Uses `EmployeeProfileService` for detailed profile tabs.
- **Actions**: Controlled profile changes route to `EmployeeProfileChangeRequest`.

### 2. Leave & Absence (`leave_balances`, `leave_applications`, `App\Domains\Absence`)
- **Integration**: Reads `leave_balances` for year, entitled, used, pending days. Queries `leave_applications` for history.
- **Actions**: Submits new leave application to `leave_applications` linked to `WorkflowInstance`.

### 3. Attendance & Scheduling (`App\Domains\Attendance`)
- **Integration**: Reads `AttendanceSession` for today's live clock state. Reads `RosterAssignment` and `ShiftDefinition` for shift details.
- **Actions**: Creates/updates `AttendanceSession` on Clock In/Out.

### 4. Payroll & Compensation (`App\Domains\Payroll`)
- **Integration**: Reads `PayrollPayslip` for latest published payslips.
- **Actions**: Securely generates breakdown via `PayslipService` with strict employee ownership check.

### 5. Employee Documents (`App\Domains\EmployeeDocuments`)
- **Integration**: Reads `EmployeeDocument` for employment/tax records. Reads `EmployeeDocumentAcknowledgement` for policy compliance.
- **Actions**: Updates acknowledgement timestamp and generates audit trail.

### 6. Learning & Performance (`App\Domains\Learning`, `App\Domains\Performance`)
- **Integration**: Reads `CourseEnrollment` for assigned/overdue courses. Reads `PerformanceGoal` and `PerformanceReview` for cycle deadlines.
- **Actions**: Direct deep links to learning player or review self-evaluation forms.

### 7. Expenses (`App\Domains\Expenses`)
- **Integration**: Reads `ExpenseClaim` where employee ID matches.
- **Actions**: Submits new claim into workflow.

### 8. Service Catalog & Helpdesk (`App\Domains\SelfService`)
- **Integration**: Reads `HrService` and `HrServiceRequest`.
- **Actions**: Submits service requests with category, attachments, and tracks SLA.

### 9. Employee AI Concierge (`App\Domains\EmployeeAi`)
- **Integration**: Reuses `EmployeeAiConciergeInterface` from Epic 2.55.
- **Actions**: Binds authenticated user/employee ID server-side to AI chat sessions.
