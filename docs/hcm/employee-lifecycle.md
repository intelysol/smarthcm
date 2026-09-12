# Flow HCM — Employee Lifecycle Management, Transfers, Promotions, Job Changes & Personnel Actions

## 1. Executive Summary
Epic 2.28 establishes an enterprise **Employee Lifecycle and Personnel Action** layer for Flow HCM.

It governs post-hire organizational movement, career promotions, compensation adjustments, and structural transfers through rigorous change-control:

```text
EMPLOYEE
   ↓
PERSONNEL ACTION REQUEST (PA-YYYY-NNNNNN)
   ↓
VALIDATION & CONFLICT DETECTION
   ↓
CROSS-DOMAIN IMPACT ANALYSIS (Core HR, Payroll, Benefits, Learning)
   ↓
WORKFLOW REVIEW & APPROVAL
   ↓
EFFECTIVE DATING (Immediate or Scheduled Future Execution)
   ↓
TRANSACTIONAL EXECUTION (Mutates Core HR & Assignment Records)
   ↓
DOWNSTREAM NOTIFICATIONS & EVENT EMISSION
   ↓
ANALYTICS & COMPENSATING REVERSAL CAPABILITY
```

---

## 2. Core Architectural Principles & System of Record Boundaries

The Lifecycle module operates strictly as an **orchestration and change-control layer**:
- **Core HR** remains authoritative for Employee, Employment, Organization, and Position Occupancy.
- **Payroll** remains authoritative for salary structures and actual payroll execution.
- **Benefits** remains authoritative for benefit enrollment and insurance tiers.
- **Time & Attendance / Leave** remain authoritative for timesheets and leave balances.
- **Workflow & Notification Center** remain authoritative for multi-step approvals and email/in-app dispatching.
- **Documents Platform** remains authoritative for letter archiving and compliance holds.

---

## 3. Personnel Action Types & Categories

Configurable action types supported:
1. `PROMOTION`: Grade advancement, title elevation, and associated salary increases.
2. `TRANSFER`: Inter-departmental, branch, or geographical relocation.
3. `JOB_CHANGE`: Position title, job family, or functional classification adjustment.
4. `COMPENSATION_CHANGE`: Base salary revision or allowance adjustment.
5. `TEMPORARY_ASSIGNMENT`: Acting appointments, secondments, and deputations with automated restoration.
6. `PROBATION_COMPLETION`: Confirmation of employment post-onboarding.
7. `PROBATION_EXTENSION`: Formal extension of probationary timeline.

---

## 4. Current State vs Proposed State & Effective Dating

Every action records normalized changes in `personnel_action_changes`:
- Captures `old_value`, `new_value`, `old_value_label`, and `new_value_label`.
- Effective Dating is mandatory: `effective_date` dictates when the change applies.
- **Future-Dated Actions**: Approved changes scheduled for future execution remain in `scheduled` status until `ProcessEffectivePersonnelActionsJob` triggers atomic mutation on the exact effective date.
- **Backdated Corrections**: Restricted to authorized operators with `personnel_actions.backdate`. Retroactive impact is calculated and logged in immutable audit records.

---

## 5. Cross-Domain Impact Analysis Engine

Prior to approval, `PersonnelActionImpactService` performs deterministic, read-only analysis across domains:
- **Core HR**: Department realignment, reporting hierarchy shifts.
- **Position**: Position occupancy validation and vacancy tracking.
- **Payroll**: Estimated monthly variance (e.g. `+$45,000 monthly`) and recalculation requirements.
- **Benefits**: Grade-triggered health insurance band and perk re-evaluation.
- **Severity Ratings**: `INFO`, `WARNING`, and `BLOCKING`.

---

## 6. Compensating Reversals & Reversal Tracking

Executed actions are **never** deleted from the database.
- Initiating a reversal creates an inverse compensating action (`REV-PA-...`).
- The compensating action inverts all changes (`new_value` $\leftrightarrow$ `old_value`).
- Execution restores Core HR state.
- Original and reversal requests are permanently linked in `personnel_action_reversals`.

---

## 7. Bulk Personnel Actions & Dry-Run Validation

Supports high-volume actions (e.g., annual company-wide increments, department mergers):
- Asynchronous batch execution via `ProcessBulkPersonnelActionBatchJob`.
- **Pre-execution Dry-Run**: Validates every record, categorizing items into `valid`, `warning`, and `error` counts before execution can proceed.

---

## 8. AI Advisory HCM Guardrails

- AI assistance is purely informational (`is_advisory => true`).
- Summarizes personnel actions, drafts formal promotion/transfer letters, and explains impact analysis.
- **Strict Guardrails**: Prohibits autonomous promotion, salary adjustments, or employment terminations.

---

## 9. Security & Authorization

- Multi-tenant data segregation.
- Granular permissions (`personnel_actions.view`, `create`, `approve`, `execute`, `reverse`, `bulk`, `backdate`, `view_compensation`).
- Compensation field masking for unauthorized viewers.
- Employee Self-Service portal strictly scoped to authenticated user context.
