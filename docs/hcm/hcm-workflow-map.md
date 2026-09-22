# Enterprise HCM Cross-Domain Workflow Map

This document establishes the end-to-end business workflow specifications across all 27 Human Capital Management (HCM) domains in the Enterprise Application Platform. 

Every workflow operates under strict **Single System of Record (SSoR)** data ownership, executing as atomic database transactions with deterministic state machines, compensating rollbacks on failure, audit logging, and asynchronous event notifications.

---

## 1. Architectural Principles

1. **Strict Single System of Record (SSoR):**
   - Core HR authoritatively owns the Employee Master identity.
   - Job Architecture authoritatively owns Position definitions, hierarchies, and headcount limits.
   - Workforce Scheduling authoritatively owns planned rosters, shifts, and coverage assignments.
   - Workforce Time & Attendance authoritatively owns raw clock events, paired time records, and overtime calculations.
   - Absence Management authoritatively owns leave types, entitlement accruals, and balance ledgers.
   - Payroll authoritatively owns calculation runs, gross-to-net processing, and payslip issuance.
   - Finance authoritatively owns general ledger mapping, payment disbursement, and settlement.
2. **Transaction Atomicity:**
   - Multi-domain transitions run within managed database transactions (`DB::transaction()`).
   - If any domain validation fails (e.g., position over-allocation, balance deficit, locked payroll period), the entire transaction aborts without orphan records.
3. **Audit Trail & Observability:**
   - Every state transition produces an immutable audit record capturing actor, tenant, timestamp, previous state, new state, and domain context.
4. **Decoupled Asynchronous Notifications:**
   - User notifications and document dispatches are queued post-commit, preventing slow external channels from stalling transactional execution.

---

## 2. Core Business Workflows (A through J)

### Workflow A: Candidate to Hired Employee
*Transition: Candidate Selection -> Offer Acceptance -> Onboarding Checklist -> Core HR Employee Record -> Position Occupancy -> IT Provisioning -> Welcome Notification*

- **Trigger:** Candidate accepts job offer in ATS / Recruitment domain.
- **Participating Domains:**
  1. Recruitment / ATS (`recruitment_job_applications`, `recruitment_offers`)
  2. Onboarding (`onboarding_workflows`, `onboarding_tasks`)
  3. Core HR (`employees`, `employee_identities`, `employee_contracts`)
  4. Job Architecture / Position Control (`positions`, `position_assignments`)
  5. Workforce Administration (`employee_work_profiles`)
  6. Service Delivery / IT Operations (`it_provisioning_requests`)
  7. Notifications & Workflow (`notifications`, `workflow_instances`)
- **Execution Flow:**
  1. Recruitment marks candidate offer status as `accepted`.
  2. Job Architecture validates that target position has `vacant` status and active headcount quota.
  3. Core HR creates immutable `employees` record with tenant isolation, generating standard enterprise employee code.
  4. Core HR assigns employee to position in `position_assignments`, setting position status to `occupied`.
  5. Onboarding domain instantiates role-specific onboarding checklist with mandatory compliance milestones.
  6. Service Delivery issues automated provisioning tickets for workspace email, hardware, and system access.
  7. Welcome email and portal access credentials dispatched to employee personal contact.
- **Compensating Rollback:** If employee creation or position assignment fails, offer state reverts to `pending_acceptance`, no partial employee record is persisted, and IT provisioning is halted.

---

### Workflow B: Employee Leave Request to Balance Deduction & Calendar Blocking
*Transition: Self-Service Request -> Manager Approval -> Leave Balance Deduction -> Absence Calendar Update -> Shift Scheduler Blackout*

- **Trigger:** Employee submits leave application via Employee Self-Service (ESS).
- **Participating Domains:**
  1. Absence Management (`leave_requests`, `leave_balances`, `leave_transactions`)
  2. Workflow Engine (`workflow_instances`, `workflow_tasks`)
  3. Workforce Scheduling (`shifts`, `shift_blackouts`, `roster_slots`)
  4. Notifications (`notifications`)
- **Execution Flow:**
  1. Absence Management checks `leave_balances` for requested leave type and date range; verifies sufficient accrued days.
  2. Leave request created with status `pending_approval`.
  3. Workflow engine assigns review task to direct line manager based on Core HR reporting line.
  4. Manager reviews and approves request (`approved`).
  5. Absence Management executes debit transaction against `leave_balances`, recording audit entry in `leave_transactions`.
  6. Workforce Scheduling registers a calendar blackout for the employee during the leave period.
  7. If employee had pre-assigned shifts during that period, shifts are flagged as `unassigned_due_to_leave` and alerted to roster planner.
  8. Approval confirmation dispatched to employee.
- **Compensating Rollback:** If balance debit fails due to concurrency collision, leave request reverts to `pending` or `rejected`, and no shifts are altered.

---

### Workflow C: Attendance Clock-in to Shift Reconciliation & Overtime to Payroll Aggregation
*Transition: Raw Clock Event -> Shift Planned vs Actual Comparison -> Overtime & Premium Calculation -> Approved Timesheet -> Payroll Earnings Input*

- **Trigger:** Biometric terminal or mobile GPS clock-in/clock-out event received.
- **Participating Domains:**
  1. Workforce Time & Attendance (`attendance_punches`, `attendance_records`, `timesheets`)
  2. Workforce Scheduling (`shifts`, `shift_templates`)
  3. Payroll (`payroll_inputs`, `payroll_line_items`)
  4. Core HR (`employees`)
- **Execution Flow:**
  1. Time & Attendance pairs in/out punches into daily `attendance_records`.
  2. System compares actual worked hours against scheduled shift from Workforce Scheduling.
  3. Policy engine computes regular hours, break deductions, tardiness, and overtime premiums based on tenancy threshold (e.g. >8 hours/day or weekend shift).
  4. Timesheet period closes; manager certifies aggregated weekly/monthly timesheet.
  5. Upon certification, Time & Attendance exports structured earnings inputs (`regular_hours`, `ot_150`, `ot_200`, `night_diff`) into Payroll `payroll_inputs`.
  6. Payroll locks input batch for upcoming payroll calculation cycle.
- **Compensating Rollback:** Disputed timesheets reject unverified overtime; payroll input generation will not ingest unapproved timesheets.

---

### Workflow D: Compensation Adjustment to Payroll Impact
*Transition: Salary Review/Promotion -> Comp Policy Approval -> Effective-dated Compensation Record -> Payroll Proration & Next Cycle Integration*

- **Trigger:** HR Administrator or Compensation Committee initiates salary adjustment.
- **Participating Domains:**
  1. Compensation (`compensation_plans`, `salary_adjustments`, `pay_grades`)
  2. Workflow Engine (`workflow_instances`)
  3. Core HR (`employee_compensation_history`)
  4. Payroll (`payroll_recurring_components`, `payroll_runs`)
- **Execution Flow:**
  1. Proposed salary adjustment validated against pay grade salary band limits.
  2. Workflow route triggered: Department Head -> Compensation Director -> Finance VP.
  3. Upon final approval, an effective-dated salary record is inserted into `employee_compensation_history`.
  4. Current compensation pointer updated in Core HR profile.
  5. Payroll synchronization checks effective date:
     - If mid-cycle, automated proration calculator generates split-period earnings lines for the next payroll run.
     - If cycle boundary, updates base recurring salary component.
- **Compensating Rollback:** Rejection cancels proposed adjustment; existing payroll recurring components remain completely unchanged.

---

### Workflow E: Learning Completion to Skill Profile & Compliance Certification
*Transition: LMS Course Complete -> Assessment Passed -> Employee Skill Matrix Updated -> Compliance Requirement Satisfied -> Manager Notification*

- **Trigger:** Employee completes all modules and passes final assessment for a certified course.
- **Participating Domains:**
  1. Learning & Development (`lms_courses`, `course_enrollments`, `course_completions`)
  2. Skills & Competencies (`employee_skills`, `skills_inventory`)
  3. Workforce Compliance (`compliance_requirements`, `compliance_certifications`)
  4. Notifications (`notifications`)
- **Execution Flow:**
  1. LMS registers course completion and scores assessment.
  2. Completion record verified; formal digital certificate generated with validity timeframe.
  3. Learning domain triggers event to Skills domain: associated skills added or proficiency level incremented in `employee_skills`.
  4. Workforce Compliance checks whether completed course satisfies mandatory statutory obligations (e.g. OSHA Safety, Anti-Harassment, Data Privacy).
  5. Compliance record updated to `compliant` with expiration date set to next recurrence cycle.
  6. Notification dispatched to employee and manager with completion summary.
- **Compensating Rollback:** Failed assessment does not grant skill or certificate; compliance flag remains in `non-compliant` or `due` status.

---

### Workflow F: Performance Appraisal to Goal Progression & Talent Rating
*Transition: Goal Cascading & Metric Tracking -> Self & Manager Review -> 9-Box Calibration -> Performance Rating Finalization -> Succession Pool Assignment*

- **Trigger:** Annual or quarterly performance appraisal cycle opens.
- **Participating Domains:**
  1. Performance Management (`performance_cycles`, `appraisals`, `goals`)
  2. Succession & Talent (`talent_pools`, `nine_box_grids`, `succession_plans`)
  3. Compensation (`merit_matrices`)
- **Execution Flow:**
  1. Performance goals evaluated against actual milestones; progress scores computed.
  2. Employee submits self-appraisal; manager completes evaluation ratings.
  3. Talent committee reviews 9-box positioning (Performance vs Potential) in calibration session.
  4. Performance appraisal finalized with certified rating score.
  5. High performers (top quadrants) automatically enrolled or updated in designated `talent_pools` for leadership succession planning.
  6. Finalized rating score feeds into Compensation merit matrix for bonus/incentive calculation.
- **Compensating Rollback:** Uncalibrated or contested reviews cannot update succession pools or trigger merit increments until formally signed off.

---

### Workflow G: Expense Submission to Multi-level Approval, Finance Ledger & Reimbursement
*Transition: Claim Incurred & Receipt Upload -> Multi-tier Approval -> GL Account Mapping & Finance Ledger Queue -> Payroll Reimbursement Settlement*

- **Trigger:** Employee submits expense reimbursement request with receipts.
- **Participating Domains:**
  1. Expense Management (`expense_reports`, `expense_items`, `expense_receipts`)
  2. Workflow Engine (`workflow_instances`)
  3. Finance / Accounting (`gl_accounts`, `finance_ledger_entries`)
  4. Payroll (`payroll_inputs`, `reimbursement_batches`)
- **Execution Flow:**
  1. Employee submits expense report categorized by expense policy code.
  2. Multi-tier approval routed: Manager approves business justification -> Finance Audits receipts and tax codes.
  3. Upon approval, expense items are mapped to specific General Ledger (GL) chart of accounts.
  4. Reimbursement route chosen (Direct AP or Payroll Addition).
  5. When routed through Payroll: non-taxable reimbursement input created in `payroll_inputs` for the employee's next active cycle.
  6. When payroll is disbursed, expense report status advances to `reimbursed`.
- **Compensating Rollback:** Disallowed expense items are rejected with reason code; no GL entry or payroll input is generated.

---

### Workflow H: Department / Location Transfer with Reporting Line Reassignment
*Transition: Transfer Request -> Origin & Destination Manager Approval -> Core HR Department/Location Update -> Hierarchy Tree Rebuild -> Workflow Delegation Re-routing*

- **Trigger:** Internal transfer request initiated for employee.
- **Participating Domains:**
  1. Core HR (`employees`, `employee_assignments`)
  2. Organizational Design (`departments`, `locations`, `reporting_lines`)
  3. Job Architecture (`positions`, `position_assignments`)
  4. Workflow Engine (`workflow_delegations`, `approval_hierarchies`)
- **Execution Flow:**
  1. Transfer request drafted with effective date, new department, new work location, and new supervisor.
  2. Approvals gathered from current department manager, receiving department manager, and HR Director.
  3. On effective date, Core HR updates employee's primary department, cost center, and location pointers.
  4. Position assignment updated in Job Architecture (vacates old position, occupies new position).
  5. Organizational reporting tree regenerated; new manager assigned.
  6. Workflow engine invalidates pending approvals awaiting old manager for this employee; reroutes active workflow tasks to new manager.
- **Compensating Rollback:** Failed position assignment in destination department rolls back employee department update; original reporting lines remain intact.

---

### Workflow I: Employee Promotion with Grade, Title, Compensation & Permission Re-scoping
*Transition: Promotion Recommendation -> Executive Approval -> Job Architecture Reclassification -> Compensation Grade Uplift -> Role & Permission Matrix Update*

- **Trigger:** Manager submits promotion nomination following performance cycle.
- **Participating Domains:**
  1. Performance & Talent (`promotions`, `career_paths`)
  2. Job Architecture (`job_families`, `job_grades`, `positions`)
  3. Compensation (`pay_grades`, `salary_adjustments`)
  4. Identity & Access Control (`users`, `roles`, `model_has_roles`, `permissions`)
  5. Core HR (`employees`, `employee_job_history`)
- **Execution Flow:**
  1. Promotion recommendation specifies new target job code, grade, title, and proposed salary adjustment.
  2. Validated against minimum tenure, performance thresholds, and departmental budget.
  3. Approval chain completed.
  4. Job Architecture reclassifies employee's job grade and title; logs entry in `employee_job_history`.
  5. Compensation increases base salary in accordance with the new grade minimum.
  6. Identity and RBAC service evaluates new position responsibilities: updates user role assignments and permission scopes (e.g. granting Manager workspace access if promoted to leadership).
  7. Formal promotion announcement dispatched to department.
- **Compensating Rollback:** If authorization update fails or salary violates grade ceiling without executive waiver, promotion commits are rolled back entirely.

---

### Workflow J: Employee Resignation / Termination to Asset Return, Final Settlement & Offboarding
*Transition: Separation Request/Notice -> Notice Period Calculation -> Clearance Checklist (IT, HR, Finance) -> Asset Recovery -> Severance & Final Pay Calculation -> Account Deactivation & Archival*

- **Trigger:** Resignation submitted by employee or termination initiated by HR.
- **Participating Domains:**
  1. Offboarding (`offboarding_cases`, `clearance_checklists`, `exit_interviews`)
  2. Asset Management / Service Delivery (`asset_assignments`, `company_assets`)
  3. Absence Management (`leave_balances`, `encashment_calculations`)
  4. Payroll (`payroll_settlements`, `final_pay_runs`)
  5. Identity & Security (`users`, `oauth_tokens`, `sessions`)
  6. Job Architecture (`positions`, `position_assignments`)
  7. Core HR (`employees`)
- **Execution Flow:**
  1. Separation case created with separation type (`resignation`, `termination`, `retirement`) and last working day.
  2. Clearance tasks dispatched concurrently:
     - **IT Clearance:** Revoke laptop, access badges, VPN, and corporate email.
     - **Facilities / Assets:** Confirm return of hardware, vehicles, corporate cards.
     - **Finance:** Clear outstanding advances or petty cash balances.
  3. Absence Management calculates accrued unused leave days eligible for encashment.
  4. Payroll Final Settlement engine processes:
     - Prorated salary for days worked in final month
     - Unused leave encashment addition
     - Outstanding loan / asset recovery deductions
     - Statutory severance and tax adjustments
  5. On final day, Identity & Access marks user account as `suspended`/`deactivated`, revoking all active sessions and OAuth tokens.
  6. Position assignment terminated in Job Architecture, reverting position to `vacant`.
  7. Core HR marks employee status as `terminated` with archival retention policy applied.
- **Compensating Rollback:** Premature termination cancellation (e.g. resignation retracted during notice period) restores employee active status, clears offboarding checklist, and keeps position occupied.

---

## 3. Workflow Transaction & Resilience Matrix

| Workflow | Primary SSoR | Participating Domains | Transaction Boundary | Failure Mode & Rollback Action | Audit Log Code |
|---|---|---|---|---|---|
| **A: Hire to Employee** | Core HR | Recruitment, Job Arch, Onboarding, IT | `DB::transaction()` around Employee + Position + Onboarding | If position occupied or validation fails, rollback candidate status to pending | `HCM_WF_HIRE_SUCCESS` |
| **B: Leave to Calendar** | Absence Mgmt | Absence, Workflow, Scheduling | `DB::transaction()` around Leave Request + Balance + Blackout | If insufficient balance, abort request with error code `INSUFFICIENT_LEAVE` | `HCM_WF_LEAVE_APPR` |
| **C: Time to Payroll** | Time & Attendance | Time, Scheduling, Payroll | Staged batch transaction: timesheet lock -> payroll input insert | If unapproved overtime detected, reject batch transfer | `HCM_WF_TIME_PAYROLL` |
| **D: Comp Adjustment** | Compensation | Comp, Workflow, Core HR, Payroll | `DB::transaction()` around Comp History + Salary pointer | If compensation outside grade band, reject adjustment | `HCM_WF_COMP_ADJUST` |
| **E: Learning to Skills** | Learning (LMS) | LMS, Skills, Compliance | Transactional event listener: complete -> skill add -> compliance flag | If assessment score below passing grade, award nothing | `HCM_WF_LMS_COMPLETE` |
| **F: Appraisal to Talent** | Performance | Performance, Talent/Succession, Comp | Atomic cycle finalization transaction | If uncalibrated ratings, block talent pool entry | `HCM_WF_PERF_FINALIZE` |
| **G: Expense to Finance** | Expense Mgmt | Expense, Workflow, GL, Payroll | Transactional ledger queue: approve -> GL map -> payroll queue | If receipt missing on mandatory category, reject claim | `HCM_WF_EXPENSE_SETTLE` |
| **H: Employee Transfer** | Core HR | Core HR, Org Design, Job Arch, Workflow | `DB::transaction()` around Org pointer + Position + Reporting line | If receiving position unavailable, preserve existing assignment | `HCM_WF_EMP_TRANSFER` |
| **I: Promotion Process** | Job Architecture | Job Arch, Comp, Core HR, RBAC | Multi-domain orchestrator transaction | If role permission mapping invalid, abort promotion | `HCM_WF_EMP_PROMOTE` |
| **J: Offboard & Settle** | Offboarding | Offboard, Assets, Absence, Payroll, Auth | Multi-step orchestrated saga with final settlement commit | If clearance blockers unresolved, hold final pay disbursement | `HCM_WF_EMP_OFFBOARD` |

---
*Certified Enterprise HCM Cross-Domain Workflow Architecture — SmartHCM Enterprise Platform*
