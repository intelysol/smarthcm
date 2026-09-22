# Enterprise HCM Cross-Domain Integration Map & Contracts

This document establishes the authoritative contract specifications and data exchange interfaces between all 27 Human Capital Management (HCM) domains in the Enterprise Application Platform.

---

## 1. Architectural Integration Principles

1. **Strict Single System of Record (SSoR):**
   - Every piece of data has exactly one Authoritative Domain (Producer).
   - All other domains are Consumers and must query or subscribe via established service contracts or database relationship foreign keys.
   - Master data duplication across domains is strictly prohibited.
2. **Tenant Isolation Boundary:**
   - Every integration payload, contract interface, and database query must enforce `tenant_id` scoping without exception.
3. **Synchronous vs Asynchronous Boundary:**
   - **Synchronous (In-Transaction / RPC Service Call):** Atomic state updates where Consumer cannot proceed without Producer confirmation (e.g. Employee creation validating Position vacancy; Leave request reserving balance).
   - **Asynchronous (Event / Queue / Webhook):** Secondary effects (e.g., sending emails, rebuilding reporting caches, calculating post-close analytics, pushing data to external third-party systems).
4. **Idempotency & Auditing:**
   - All state-changing integration calls include an `idempotency_key` or unique reference to prevent duplicate processing on retries.
   - Cross-domain calls log transaction telemetry to `audit_logs` or `integration_sync_logs`.

---

## 2. 27-Domain Producer / Consumer Matrix

| Domain # | Domain Name | Authoritative Data (Produces) | Consumes Data From | Interface Type |
|---|---|---|---|---|
| **01** | **Core HR** | Employee Identity, Code, Personal Info, Demographics, Employment Status, Work Contracts | Org Design, Job Arch | SSoR Primary Producer |
| **02** | **Org Design** | Departments, Business Units, Cost Centers, Locations, Reporting Hierarchy | Core HR (Count) | Synced Hierarchy Provider |
| **03** | **Job Arch** | Job Families, Job Grades, Job Titles, Positions, Headcount Limits | Org Design | Position Control Service |
| **04** | **Recruitment** | Candidates, Vacancies, Job Postings, Application Stages, Offer Letters | Job Arch, Core HR | Event & Service Call |
| **05** | **Onboarding** | Onboarding Plans, Checklists, Task Tracking, New Hire Kit | Core HR, IT Ops | Event-Driven Consumer |
| **06** | **Time & Attend** | Punches, Paired Records, Regular Hours, Overtime, Timesheets | Scheduling, Core HR | Sync Punch + Batch Engine |
| **07** | **Absence Mgmt** | Leave Types, Accrual Policies, Balances, Approved Leaves | Core HR, Scheduling | Atomic Balance Ledger |
| **08** | **Workforce Sched** | Shift Patterns, Rosters, Work Schedules, Blackout Periods | Core HR, Absence | Roster Planning Engine |
| **09** | **Payroll** | Pay Cycles, Gross Pay, Deductions, Net Pay, Payslips, Bank Files | Core HR, Time, Absence, Comp, Expenses | Batch Processor & SSoR |
| **10** | **Compensation** | Salary Bands, Pay Grades, Merit Grids, Compensation Histories | Job Arch, Perf, Payroll | Policy & Rule Service |
| **11** | **Benefits** | Benefit Plans, Coverage Options, Enrollments, Dependents | Core HR, Payroll | Benefit Admin Service |
| **12** | **Performance** | Review Cycles, Self/Manager Reviews, 360 Feedback, Goals, Ratings | Core HR, Talent, Comp | Appraisal Lifecycle Service |
| **13** | **Goals & OKRs** | Organizational Objectives, Department OKRs, Key Results | Core HR, Org Design | Cascading Objective Engine |
| **14** | **Talent & Succ** | Talent Pools, 9-Box Grids, Critical Positions, Succession Plans | Core HR, Perf, Job Arch | Talent Intelligence Service |
| **15** | **Learning (LMS)** | Courses, Modules, Assessments, Enrollments, Certifications | Core HR, Skills, Compliance | LMS Engine & Event Bus |
| **16** | **Skills & Comp** | Skill Dictionary, Employee Competencies, Skill Gap Analysis | Core HR, LMS, Perf | Competency Repository |
| **17** | **Career Pathing** | Career Ladders, Progression Milestones, Role Readiness | Job Arch, Skills, LMS | Career Mapping Service |
| **18** | **Offboarding** | Separation Cases, Clearance Workflows, Exit Interviews | Core HR, Assets, Payroll | Multi-Step Exit Saga |
| **19** | **Expenses** | Expense Claims, Policy Rules, Mileage/Per Diem, Receipts | Core HR, Finance, Payroll | Reimbursement Workflow |
| **20** | **HR Service Del** | Service Requests, Helpdesk Tickets, HR Knowledge Base, SLAs | Core HR, Identity | Ticket & Workflow System |
| **21** | **Employee Docs** | Electronic Document Archive, Templates, e-Signatures | Core HR, All Domains | Document Repository SSoR |
| **22** | **Health & Safety** | Incident Reports, OSHA Logs, Workplace Hazards, Clinic Visits | Core HR, Locations | Safety Tracking System |
| **23** | **Compliance** | Statutory Requirements, Mandated Trainings, Policy Signoffs | Core HR, LMS, Documents | Compliance Auditor Service |
| **24** | **Workforce Cost** | Labor Cost Forecasts, Headcount Budgets, Variance Tracking | Core HR, Payroll, Finance | Budget Modeling Engine |
| **25** | **Productivity** | Activity Metrics, Output Indicators, Utilization Rates | Time, Tasks, Core HR | Telemetry Aggregator |
| **26** | **Optimization** | Shift Optimization, Overtime Control, Staffing Recommendations | Scheduling, Time, Cost | AI & Heuristic Optimizer |
| **27** | **HR Analytics** | Executive Dashboards, Turnover Metrics, Headcount Trends | All 26 Domains | Read-Only OLAP Warehouse |

---

## 3. High-Traffic Contract Specifications

### 3.1 Recruitment -> Core HR (Candidate Hire Contract)
- **Producer:** ATS / Recruitment
- **Consumer:** Core HR, Job Architecture, Onboarding
- **Contract Type:** Synchronous Orchestration (`HcmWorkflowOrchestrationService::hireCandidateToEmployee`)
- **Payload Schema:**
```json
{
  "tenant_id": "UUID",
  "candidate_id": "UUID",
  "offer_id": "UUID",
  "position_id": "UUID",
  "department_id": "UUID",
  "first_name": "string",
  "last_name": "string",
  "personal_email": "email",
  "work_email": "email",
  "hire_date": "YYYY-MM-DD",
  "initial_salary": 85000.00,
  "currency": "USD"
}
```
- **Guarantees:** Atomic creation of `employee` record, position occupancy lock, and onboarding task generation.

---

### 3.2 Absence Management -> Workforce Scheduling (Leave Blackout Contract)
- **Producer:** Absence Management
- **Consumer:** Workforce Scheduling
- **Contract Type:** Synchronous In-Transaction Call
- **Payload Schema:**
```json
{
  "tenant_id": "UUID",
  "employee_id": "UUID",
  "leave_request_id": "UUID",
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD",
  "leave_type": "ANNUAL|SICK|MATERNITY|UNPAID",
  "hours_per_day": 8.0
}
```
- **Guarantees:** Registers calendar conflict; marks overlapping published shifts as unassigned; blocks roster assignment.

---

### 3.3 Workforce Time -> Payroll (Pay Input Aggregation Contract)
- **Producer:** Workforce Time & Attendance
- **Consumer:** Payroll Processing Engine
- **Contract Type:** Certified Batch Transfer
- **Payload Schema:**
```json
{
  "tenant_id": "UUID",
  "payroll_cycle_id": "UUID",
  "period_start": "YYYY-MM-DD",
  "period_end": "YYYY-MM-DD",
  "entries": [
    {
      "employee_id": "UUID",
      "regular_hours": 160.00,
      "overtime_standard_hours": 12.50,
      "overtime_double_hours": 4.00,
      "unpaid_absence_hours": 0.00,
      "night_shift_differential_hours": 8.00
    }
  ]
}
```
- **Guarantees:** Timesheets must be approved before export; locks time records against retroactive edits.

---

### 3.4 LMS -> Skills & Compliance (Certification Event Contract)
- **Producer:** Learning & Development (LMS)
- **Consumer:** Skills Inventory, Workforce Compliance
- **Contract Type:** Domain Event (`CourseCompletedEvent`)
- **Payload Schema:**
```json
{
  "tenant_id": "UUID",
  "course_id": "UUID",
  "employee_id": "UUID",
  "completion_date": "YYYY-MM-DDTHH:MM:SSZ",
  "score_percent": 95.0,
  "passed": true,
  "awarded_skills": [
    {"skill_id": "UUID", "proficiency_level": "intermediate"}
  ],
  "satisfies_compliance_codes": ["OSHA-101", "INFOSEC-2026"]
}
```
- **Guarantees:** Idempotent ingestion based on `(course_id, employee_id, completion_date)`.

---

### 3.5 Expense Management -> Payroll (Reimbursement Settlement Contract)
- **Producer:** Expense Management
- **Consumer:** Payroll Engine
- **Contract Type:** Staged Queue Interface
- **Payload Schema:**
```json
{
  "tenant_id": "UUID",
  "expense_report_id": "UUID",
  "employee_id": "UUID",
  "approved_amount": 420.50,
  "currency": "USD",
  "gl_cost_center": "CC-ENG-001",
  "reimbursement_method": "payroll_cycle",
  "effective_cycle_date": "YYYY-MM-DD"
}
```
- **Guarantees:** Non-taxable earnings line item created in active payroll run; marks expense report as `queued_for_payroll`.

---

## 4. Error Handling, Circuit Breakers & Dead Letter Queues

1. **Transaction Abort:** When synchronous cross-domain actions encounter constraint violations (e.g. invalid position, insufficient balance, closed financial period), the controller/service throws a domain exception inheriting from `DomainValidationException`.
2. **Asynchronous Retry:** Asynchronous domain events run on Laravel Queues with `tries = 3` and exponential backoff (`backoff = [10, 60, 300]`).
3. **Dead Letter Queue (DLQ):** Messages failing after maximum retries are routed to `failed_jobs` and trigger an alert in the Platform Admin event monitor.
4. **Idempotency Safeguard:** Consumer handlers store processed event UUIDs in `domain_processed_events` for 30 days to prevent dual-processing.

---
*Certified Enterprise HCM Integration Architecture — SmartHCM Enterprise Platform*
