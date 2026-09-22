# EPIC 2.61 — Security & Data Protection Specification
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

---

## 1. Security Architecture & Boundary Enforcement

Epic 2.61 enforces a strict, defense-in-depth security model across five critical boundaries:

### 1. Multi-Tenant Isolation
All queries MUST include `tenant_id` filtering. Cross-tenant leakage is strictly prevented through tenant scoping on models and services.

### 2. Horizontal Employee Self-Service Isolation
- An employee can only view, track, or comment on cases where `employee_id == Auth::user()->employee->id`.
- Attempts by Employee A to access Employee B's case records, document attachments, or timeline details are rejected with `403 Forbidden`.

### 3. Manager Scope Boundaries
- A people manager can view service requests originating from their direct or indirect reports (`reporting_manager_id == manager->id` or via reporting hierarchy).
- Managers CANNOT view sensitive employee-relations matters (e.g. harassment, disciplinary investigations, confidential grievances) unless explicitly appointed as an authorized participant in the case.

### 4. Dual-Channel Message Protection (Internal Notes)
- `hr_service_request_comments` supports `comment_type: 'public'` and `comment_type: 'internal'`.
- Front-end portal views rendered for employees, and API endpoints serving employee requests, MUST filter out `internal` comments (`where('comment_type', 'public')`).
- Internal case collaboration notes, agent-to-agent mentions, and triage debates remain strictly protected from employee visibility.

### 5. Sensitive Case & Need-to-Know Protection
- Cases flagged with `confidentiality_level: 'restricted'` or `'confidential'` (such as employee relations, disciplinary matters, medical disclosures) require specific permissions (`hr.er.view`, `hr.legal.view`).
- When a service request escalates to an ER Case via `convertToHrCase`, the originating service request status is marked `RESOLVED`, and access to the resulting `EmployeeRelationCase` is transferred to authorized investigators and compliance officers only.

---

## 2. AI Safety & Privacy Guardrails

1. **Advisory Role Only**:
   - The AI Assistant provides knowledge recommendations, case summaries, and suggested response drafts.
   - The AI engine does NOT have autonomous authority to approve requests, resolve complaints, determine guilt, or trigger disciplinary actions.
2. **Restricted Indexing**:
   - Confidential case notes and restricted evidence files are excluded from general RAG and search indexes.
