# Epic 2.33 — HCM Employee Compliance, Work Permits, Visas, Licenses, Regulatory Eligibility & Workforce Compliance

## 1. Architecture Overview

Workforce Compliance operates as an **Orchestration, Eligibility, and Monitoring Layer** inside Flow Enterprise HCM. It establishes whether an employee is legally and regulatorily authorized to perform work within a specific country, legal entity, branch, department, or job role.

### Authoritative Boundaries
- **Core HR (`Employee`, `Department`, `Position`, `Employment`):** Authoritative source for employee identity, employment status, job location, citizenship, and organizational hierarchy.
- **Document Management (`Document`):** Authoritative for physical/digital file storage, SHA-256 hashes, virus scanning, and storage versions. Compliance records store document UUID references (`document_id`).
- **Learning Management System (LMS):** Authoritative source for training courses, curriculums, and certifications. Compliance checks LMS completion records to satisfy mandatory training requirements without duplicating educational history.
- **Compliance Orchestration Layer:** Owns compliance definitions, applicability rules, verification lifecycles, renewal tracking, expiration pipelines, and controlled exemption registries.

---

## 2. Core Entities & Normalized Schema

| Table Name | Entity Purpose |
|------------|----------------|
| `hcm_compliance_requirement_types` | Master taxonomy (Work Permit, Visa, License, Regulatory Certification, Medical Clearance, Background Check, Mandatory Training, Policy Attestation, Industry Accreditation). |
| `hcm_compliance_requirements` | Tenant requirement definitions with country, legal entity, department, position, renewal frequency, grace periods, and verification flags. |
| `hcm_employee_compliance_requirements` | Employee-specific assigned obligations with status, satisfaction timestamp, and due dates. |
| `hcm_employee_work_permits` | Work authorization records with permit number, issuing country, visa linkage, conditions, and expiration. |
| `hcm_employee_visa_records` | Immigration visas and residency status records with entries allowed, sponsor name, and travel restrictions. |
| `hcm_employee_licenses` | Professional and occupational licenses with licensing board, license number, and primary-source verification status. |
| `hcm_employee_registrations` | Regulatory body registrations with registration body name and validity periods. |
| `hcm_compliance_verifications` | Immutable primary-source and registry verification checks with method, reviewer, and next verification dates. |
| `hcm_compliance_renewals` | End-to-end renewal lifecycles with submission deadlines, tracking numbers, renewal costs, and document attachments. |
| `hcm_compliance_exemptions` | Controlled exemption registry with mandatory business justifications, approver IDs, and time-bounded validity. |
| `hcm_employee_compliance_snapshots` | Point-in-time cached scores (0–100%) and breach counters for high-speed reporting. |
| `hcm_compliance_tasks` | Operational to-dos dispatched to employees, HR operations, and legal teams. |
| `hcm_compliance_escalations` | Automated notification and breach escalation logs across 90/60/30-day horizons. |
| `hcm_compliance_audits` | Immutable audit trail for all changes to compliance status and requirements. |

---

## 3. Expiration Management & Automated Escalations

The scheduler executes `CheckComplianceExpirationsJob` and `ProcessComplianceEscalationsJob`:
- **T-90 Days:** Dispatches initial renewal notice to the employee.
- **T-60 Days:** Dispatches manager and HR alert; automatically spawns a compliance renewal task.
- **T-30 Days:** Escalates to high severity with alert to the enterprise Compliance Officer.
- **T-0 Days (Expired):** Status changes to `expired`/`breached`, marking the employee as non-compliant and locking downstream personnel actions if configured.

---

## 4. Controlled Exemption Protocol

No employee or requirement is exempt automatically.
- Every exemption requires:
  1. Detailed justification and business rationale.
  2. Authorized executive approval (`approved_by`).
  3. Strict validity dates (`valid_from` to `valid_until`).
- Exemptions can be explicitly approved, rejected, or revoked. Revoking an exemption immediately triggers automatic re-evaluation of the employee's compliance posture.

---

## 5. API Endpoints Reference

All endpoints are prefixed with `/api/v1/hcm/compliance` and require authentication.

- `GET /dashboard/stats` — Summary metrics, portfolio counts, and expiration horizons.
- `GET /requirements` / `POST /requirements` — Requirements catalog.
- `GET /employees/{id}/compliance` — Employee assigned obligations and real-time status.
- `POST /employees/{id}/compliance/evaluate` — Trigger recalculation of employee compliance score.
- `GET /work-permits` / `POST /work-permits` — Work authorization permits.
- `GET /visas` / `POST /visas` — Visas and residency permits.
- `GET /licenses` / `POST /licenses` — Professional licenses.
- `POST /verifications` — Primary source verification workflow.
- `POST /renewals` / `POST /renewals/{id}/complete` — Renewal lifecycle operations.
- `POST /exemptions` / `POST /exemptions/{id}/approve` — Controlled exemption governance.
- `POST /bulk/assign` / `POST /bulk/preview` — Bulk assignment and dry-run import preview.
- `GET /reports/work-permits` / `GET /reports/non-compliant` — Operational compliance reports.
- `GET /ai/explain/{id}` — Advisory compliance explanation.
