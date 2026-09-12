# Security, Multi-Tenancy & Access Control

## Multi-Tenant Boundary Isolation
Every table within the HR Service Delivery & Shared Services module enforces strict multi-tenant isolation via `tenant_id` foreign keys and Eloquent query scoping. Cross-tenant leakage is architecturally impossible.

---

## Role-Based Access Control (RBAC)
* **Employee (`role:employee`)**:
  * Can view own requests and history.
  * Can submit requests, reply to comments, rate CSAT, and download issued documents.
  * Zero access to internal notes or queue dashboards.
* **Manager (`role:manager`)**:
  * Can view and approve team members' requests requiring line manager approval.
* **Shared Services Tier 1 Agent (`role:hr_shared_services_agent`)**:
  * Access to Assigned and Tier 1 Queues.
  * Can add internal notes, request employee info, merge duplicates, and resolve routine requests.
  * No access to `restricted` or Employee Relations confidential cases.
* **Specialist / Tier 2 Agent (`role:payroll_specialist`, `role:benefits_specialist`)**:
  * Access to specialized domain queues (Payroll, Benefits).
* **Employee Relations Specialist / Legal (`role:er_specialist`, `role:hr_director`)**:
  * Sole authorized access to `restricted` confidentiality level cases, investigation interviews, and disciplinary actions.

---

## Internal Note Privacy & Confidentiality Flags
* **Internal Comments (`comment_type = 'internal'`)**: Visible only to authorized HR operations staff. Filtered out from all employee self-service endpoints.
* **Confidentiality Levels**:
  * `normal`: Standard open operational ticket.
  * `confidential`: Limited to manager and HR.
  * `highly_confidential`: Limited to designated HR operations specialists.
  * `restricted`: Strictly isolated to authorized Employee Relations / Compliance investigators.
