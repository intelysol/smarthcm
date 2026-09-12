# HCM Workforce Administration, Global HR Operations & HR Governance (Epic 2.40)

## 1. Overview
The **HCM Workforce Administration & Global HR Operations** module provides HR administrators, shared service teams, and authorized HR operations specialists with a centralized operational control and governance plane across the SmartHCM platform.

It acts as the single pane of glass to:
- Monitor workforce administration and lifecycle transitions.
- Manage configurable operational HR queues and task items.
- Identify incomplete employee records and data quality anomalies.
- Monitor upcoming effective-dated and high-risk backdated changes.
- Manage HCM operational exceptions and resolution lifecycles.
- Orchestrate governed, multi-stage bulk HR operations with dry-run validation.
- Track service-level agreement (SLA) targets and escalation breaches.
- Execute cross-domain reconciliation checks against Payroll, Benefits, Compliance, Documents, and Expenses.
- Supervise HCM configuration health and integration feeds.

---

## 2. Core Architectural Philosophy
> **The Key Rule:** HCM Operations observes, coordinates, governs, and initiates. The underlying HCM domains remain authoritative.

Workforce Administration **NEVER** duplicates domain master data:
- **Core HR** remains the authoritative master for Employees, Positions, Jobs, Organizations, and Managers.
- **Personnel Actions / Lifecycle** owns transfers, promotions, job changes, and effective-dated requests.
- **Payroll** owns compensation structures, payroll cycles, and tax withholdings.
- **Benefits** owns plan enrollment and life-event elections.
- **Compliance** owns work permits, visas, and regulatory requirements.
- **Workflow & Rules** owns approval routing and business policy logic.
- **Documents** owns physical file storage and retention policies.

---

## 3. Documentation Index
- [Architecture & Control Plane](architecture.md)
- [Domain Model & Entities](domain-model.md)
- [HR Operations Cockpit](operations.md)
- [Operational Queues](queues.md)
- [Exception Workspace](exceptions.md)
- [HR Governance Controls](governance.md)
- [Data Quality Engine](data-quality.md)
- [Bulk Operations & Dry Run](bulk-operations.md)
- [Cross-Domain Reconciliation](reconciliation.md)
- [Service Level Management (SLA)](sla.md)
- [HR Calendar & Operational Deadlines](calendar.md)
- [Integration Monitoring](integrations.md)
- [REST API Specifications](api.md)
- [Security, RBAC & Isolation](security.md)
- [Automated Testing & Quality](testing.md)
- [AI Advisory Guardrails](ai.md)
