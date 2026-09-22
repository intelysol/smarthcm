# EPIC 2.61 — Product Requirements Document (PRD)
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

---

## 1. Executive Summary & Vision

The objective of **Epic 2.61** is to build the unified, enterprise-grade **HR Service Delivery Operating Layer & Command Center** connecting Employee Self-Service (ESS), Manager Workbench (MSS), HR Shared Services Helpdesk, Case Management, Service Catalog, Employee Lifecycle, Workflow, Documents, Notifications, Knowledge Base, AI Concierge, and Analytics.

This layer allows HR operations teams to triage, assign, investigate, process, escalate, and resolve workforce cases through a single operational command center while employees and managers continue using their unified digital workplace from Epic 2.60.

---

## 2. Guiding Principles

1. **Orchestration Over Duplication**:
   - The capability acts as an orchestration and service delivery layer.
   - It reuses existing authoritative domain models (`hr_service_requests`, `hr_service_definitions`, `hr_service_queues`, `hr_service_sla_instances`, `hr_knowledge_articles`, and `employee_relation_cases`).
   - No duplicate case engines, rules engines, workflow engines, or document storage systems are created.
2. **Horizontal & Role Isolation**:
   - Employees see only their authorized cases/requests.
   - Managers see team service requests within their direct/indirect reporting scope.
   - HR agents work from assigned queues and ticket pools with SLA tracking.
   - Highly sensitive employee-relations matters (harassment, investigations, disciplinary) are strictly isolated with need-to-know access control.
3. **AI Safety & Non-Autonomous Decision Making**:
   - AI Concierge and Case Summarization serve purely advisory and assistance roles.
   - Autonomous determination of disciplinary actions, employee guilt, terminations, or medical fitness is strictly prohibited.

---

## 3. Core Functional Capabilities

### 3.1 HR Service Delivery Command Center
A comprehensive operational dashboard providing real-time visibility into:
- **Case Volume & Health**: Open cases, new cases today, overdue cases, due soon, unassigned, escalated, awaiting employee, awaiting approval, resolved today.
- **SLA Performance**: Current SLA compliance percentage, average resolution time, response time.
- **Queue Workload & Capacity**: Active ticket distribution across queues (General HR, Payroll Support, Benefits, Employee Relations, Documents, Attendance), member capacity utilization, and over-capacity risk alerts.
- **Deflection & Knowledge**: Deflection rate, self-service transactions completed, knowledge article helpfulness.
- **Customer Satisfaction (CSAT)**: Aggregate CSAT score (1–5 stars), response rate, and dimension ratings (timeliness, knowledge, helpfulness).

### 3.2 Case Inbox & Workbench
- Unified case inbox with multi-dimensional filtering (status, priority, queue, category, agent, employee, department, SLA, due date).
- Standard case lifecycle statuses: `NEW`, `ASSIGNED`, `IN_PROGRESS`, `WAITING_FOR_EMPLOYEE`, `WAITING_FOR_APPROVAL`, `RESOLVED`, `CLOSED`, `CANCELLED`.
- Chronological timeline capturing the complete case provenance (creation, assignments, status changes, comments, document uploads, SLA events, approval steps).
- Dual-channel communication: Public messages (visible to employee in ESS) vs Confidential Internal Notes (restricted to HR agents only).

### 3.3 Dynamic Service Catalog & Case Generation
- Configuration-driven catalog covering Employment Certificates, Salary Certificates, Bank Detail Modifications, Leave Assistance, Benefits Inquiries, Tax Documents, and General Policy questions.
- Dynamic form schema capture with automated context resolution from employee master (company, department, branch, cost center, manager).
- Automated routing rule evaluation and assignment to appropriate operational queues.

### 3.4 SLA Management & Escalation Engine
- Response and Resolution SLA clocks with support for business hours and paused states (`WAITING_FOR_EMPLOYEE`).
- Multi-tier escalation thresholds: Warning at $\ge 80\%$ time consumed, Escalation at $\ge 100\%$ breach.

### 3.5 Grounded AI Case Assistant
- Case summarization synthesizing Issue, Timeline, Employee Request, Actions Taken, Missing Info, Current Status, SLA, and Next Recommended Step with citations.
- Advisory suggested knowledge articles to guide faster agent resolution.

---

## 4. Personas & Permissions

| Persona | Allowed Access |
|---|---|
| **Employee** | View service catalog, submit requests, view own cases, converse on public messages, rate resolved cases (CSAT). |
| **Manager** | View team requests within direct/indirect reporting scope, approve/reject requests, view non-sensitive team service status. |
| **HR Agent** | Access assigned queue, claim unassigned cases, communicate publicly and internally, attach documents, transition status, resolve cases. |
| **HR Manager / Admin**| Full Command Center visibility, queue capacity management, SLA policy configuration, escalation override, sensitive case access. |
