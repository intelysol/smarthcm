# EPIC 2.60 — Security & Privacy Architecture
## HCM Employee & Manager Experience Layer

---

## 1. Zero Trust Identity Context
1. **Server-Side Identity Binding**:
   - The logged-in employee identity is strictly derived on the server from the authenticated session:
     ```php
     $employee = Employee::where('tenant_id', Auth::user()->tenant_id)
         ->where('user_id', Auth::id())
         ->firstOrFail();
     ```
   - Client requests supplying an arbitrary `employee_id` in query parameters or request bodies are ignored or rejected for all `/api/me/*` endpoints.

2. **Tenant Isolation**:
   - Every database query in the experience layer enforces `where('tenant_id', $tenantId)`.
   - Cross-tenant data retrieval is strictly impossible.

---

## 2. Horizontal Authorization (Employee Isolation)
- **Rule**: Employee A can NEVER access Employee B's private information.
- **Enforcement**:
  - Payslips: Validated by `$payslip->employee_id === $currentEmployee->id`.
  - Documents: Validated by `$document->employee_id === $currentEmployee->id`.
  - Personal Information / Bank: Direct fields are read from `$currentEmployee`.
  - Tasks & Requests: Filtered strictly by `$employee->id`.

---

## 3. Manager Scope Authorization
- **Rule**: Managers can only view and act on direct and indirect reports within their organization branch.
- **Enforcement**:
  - In `ManagerWorkbenchService`, all team endpoints verify that the target employee's `reporting_manager_id` matches the current manager, or is part of the manager's departmental hierarchy.
  - An attempt by a manager to approve an employee's request outside their scope results in HTTP 403 Forbidden.
  - Self-approval restriction: A manager cannot approve their own requests (Leave, Expense) unless explicitly configured by policy.

---

## 4. Sensitive Document & Payroll Protection
- **No Predictable URLs**: Payslip and document files are never served via public static URLs.
- **Streamed Through Controller**: Files are served through an authenticated controller verifying identity and logging an audit event (`hcm_experience_audits`).
- **Data Masking**: National ID, Bank Account number, and salary details are masked in UI views unless the user explicitly reveals them with verification.

---

## 5. AI Concierge Grounding & Safety
- **Context Injection**: The AI prompt is fed only the authenticated employee's verified context.
- **Restricted Tools**: The AI is equipped with read-only tools scoped to that employee. It cannot execute mutations (e.g. submitting a loan or terminating employment) without explicit, authenticated user confirmation through standard workflow forms.
