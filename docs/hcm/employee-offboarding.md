# Flow HCM — Offboarding, Separation, Resignation, Termination, Retirement & Employee Exit Management

## 1. Executive Summary
Epic 2.29 implements an enterprise-grade **Offboarding and Employee Exit Management** orchestration layer for Flow HCM.

It governs the entire post-employment transition:

```text
EMPLOYEE
   ↓
SEPARATION REQUEST (SEP-YYYY-NNNNNN)
   ↓
DETERMINISTIC IMPACT ANALYSIS (Core HR, Payroll, Leave, Assets, Benefits, ER)
   ↓
NOTICE PERIOD MANAGEMENT (Calculation, Overrides, Buyout, Garden Leave)
   ↓
MULTI-DEPARTMENT CLEARANCE MATRIX (HR, Finance, IT, Assets, Admin, Security)
   ↓
HANDOVER & KNOWLEDGE TRANSFER VERIFICATION
   ↓
FINAL SETTLEMENT SNAPSHOT (Payroll Orchestration)
   ↓
ATOMIC EXECUTION ON LAST WORKING DAY / EFFECTIVE DATE
   ↓
EXIT DOCUMENT GENERATION (Relieving Letters, Experience Certificates)
   ↓
POST-EXIT PORTAL (Limited Retention Access)
```

---

## 2. Architectural Principles & System of Record Boundaries

The Offboarding domain functions strictly as an **orchestration and exit-control layer**:
- **Core HR**: Authoritative owner of `Employee`, `Employment`, `Department`, and `Position Occupancy`. Status updates to `separated`, `retired`, or `terminated` occur exclusively upon atomic execution.
- **Payroll**: Authoritative owner of gross-to-net calculations, statutory deductions, and final salary runs. Offboarding captures an immutable settlement snapshot reference (`separation_final_settlements`).
- **Leave**: Authoritative owner of accrued leave balances for encashment or recovery.
- **Assets / Inventory**: Authoritative owner of physical asset assignments and returns.
- **Employee Relations**: Authoritative owner of disciplinary investigations and confidential records. Offboarding holds a safe reference (`er_case_reference_id`) while masking details from unauthorized eyes.
- **Document Management**: Authoritative store for signed separation letters and service certificates.

---

## 3. Supported Separation Types

Configurable separation types supported:
1. `RESIGNATION`: Voluntary employee self-service resignation with proposed LWD and review.
2. `INVOLUNTARY_TERMINATION`: Authorized termination requiring `separations.terminate` permission.
3. `RETIREMENT`: Voluntary or statutory retirement with extended notice rules.
4. `CONTRACT_EXPIRY`: Automated contract expiration reminder and review workflow.
5. `MUTUAL_SEPARATION`: Agreed separation terms and expedited clearance.
6. `REDUNDANCY`: Structural reorganization and severance package referencing.
7. `DEATH_IN_SERVICE`: Compassionate estate settlement and beneficiary processing.
8. `TRANSFER_OUT`: Inter-entity transfer across group tenants.

---

## 4. Notice Period & Date Modeling

The platform clearly models distinct date concepts:
- **Requested Last Working Day**: Proposed by the employee or requester.
- **Calculated Last Working Day**: Derived from policy notice days (`notice_days_default`).
- **Approved Last Working Day**: Formally approved by HR and manager.
- **Actual Last Working Day**: Recorded upon physical exit execution.
- **Separation Effective Date**: Date on which Core HR master records are mutated.
- **Garden Leave**: Employee remains employed but relieved of active duties.
- **Notice Overrides**: Requires `separations.notice_override` permission, formal justification, old/new day tracking, and immutable auditing.

---

## 5. Multi-Department Clearance Matrix

Clearance tracking spans 6 specialized functional domains:
- **Human Resources**: Exit interview completion, ID surrender, experience letter check.
- **Finance**: Loan/advance recovery, corporate card return, travel expense settlement.
- **Information Technology**: Laptop collection, SaaS access decommissioning, email archiving.
- **Asset Management**: Office keys, fobs, safety gear, and specialized tools surrender.
- **Administration & Security**: Building access deactivation, parking permit return.
- **Status States**: `PENDING`, `CLEARED`, `REJECTED`, `WAIVED`, `BLOCKED`.
- **Waiver Control**: Requires `separations.clearance_waive` permission with mandatory rationale.

---

## 6. Compensating Reversals & Reinstatement

- Exited employee records are **never deleted**.
- Reversals and reinstatements utilize compensating workflows:
  - Reverts Core HR status to `active`.
  - Clears `termination_date`.
  - Restores primary `EmployeeAssignment`.
  - Permanently links original and reversal records in `separation_reversals`.

---

## 7. AI Advisory HCM Guardrails

- Advisory only (`is_advisory => true`).
- Summarizes clearance bottlenecks, drafts formal relieving letters, and extracts exit interview sentiment themes.
- **Strict Guardrails**: Prohibits autonomous decisions regarding employee termination, redundancy candidate selection, or severance calculation.

---

## 8. Security & Compliance

- Multi-tenant data isolation.
- Granular permissions (20+ permissions under `separations` group).
- Segregation of duties: separation requester, approver, and executor.
- Post-exit self-service access strictly limited to authorized documents, payslips, and tax forms.
