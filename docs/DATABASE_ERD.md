# Enterprise HCM — Entity Relationship Diagrams (ERD) Guide

> High-resolution architectural and domain-level entity relationship diagrams generated directly from the authoritative 975-table schema.

---

## 1. ERD Hierarchy

Because the Enterprise HCM schema encompasses **975 tables** and **2,132 foreign-key constraints**, visualization is organized across three progressive levels of abstraction:

```text
┌────────────────────────────────────────────────────────┐
│ LEVEL 1: Enterprise Domain Overview Diagram            │
│ docs/erd/enterprise_hcm_domain_erd.svg                 │
│ Shows 28 major functional domains & inter-domain flows │
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│ LEVEL 2: Functional Domain ERDs                        │
│ docs/erd/*.svg                                         │
│ Detailed entity structures, columns, PK, FK, datatypes │
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│ LEVEL 3: Complete Unified Enterprise ERD               │
│ docs/erd/enterprise_hcm_full_erd.svg                   │
│ Massive high-resolution vector graph of all 975 tables │
└────────────────────────────────────────────────────────┘
```

---

## 2. Level 1: Enterprise Domain Architecture Diagram

- **File Path**: [`docs/erd/enterprise_hcm_domain_erd.svg`](file:///c:/laragon/www/smarthcm/docs/erd/enterprise_hcm_domain_erd.svg)
- **Scope**: Maps the 28 core enterprise modules, total table counts per domain, and high-level architectural relationships.
- **Key Domains Represented**:
  - Identity & Security
  - Tenancy & Administration
  - Organization & Hierarchy
  - Employee Core
  - Time & Attendance
  - Leave Management
  - Payroll Engine
  - Compensation & Planning
  - Benefits & Wellness
  - Expense & Travel
  - Talent Acquisition
  - Learning & Development
  - Performance & Succession
  - Employee Relations
  - Document Management
  - HR Service Delivery
  - Workforce Analytics & Planning
  - Command Center & Intelligence
  - Data Governance & Metadata
  - Responsible AI Governance
  - AI Operations & Concierge
  - Global Mobility
  - Health & Safety
  - Absence & Return to Work
  - Workflow Engine
  - Integration Hub & Public APIs
  - Platform Operations

---

## 3. Level 2: Domain Entity Relationship Diagrams

High-detail vector diagrams displaying entity schemas, column datatypes, primary keys (`[PK]`), foreign keys (`[FK]`), and cardinality relationships:

1. **Employee Core & Organization**: [`docs/erd/employee-core.svg`](file:///c:/laragon/www/smarthcm/docs/erd/employee-core.svg)
   - Entities: `employees`, `companies`, `departments`, `positions`, `employee_bank_accounts`, `employee_documents`, `employee_timelines`.
2. **Payroll & Compensation**: [`docs/erd/payroll.svg`](file:///c:/laragon/www/smarthcm/docs/erd/payroll.svg)
   - Entities: `payroll_runs`, `payroll_entries`, `payroll_calculation_lines`, `payroll_payslips`, `compensation_structures`, `compensation_bands`.
3. **Time, Attendance & Leave**: [`docs/erd/attendance.svg`](file:///c:/laragon/www/smarthcm/docs/erd/attendance.svg)
   - Entities: `shifts`, `shift_rosters`, `attendance_policies`, `daily_attendances`, `leave_applications`, `leave_balances`.
4. **Talent Acquisition & Recruitment**: [`docs/erd/recruitment.svg`](file:///c:/laragon/www/smarthcm/docs/erd/recruitment.svg)
   - Entities: `requisitions`, `job_postings`, `candidates`, `applications`, `hcm_recruitment_interviews`, `evaluations`.
5. **Benefits & Financial Wellness**: [`docs/erd/benefits.svg`](file:///c:/laragon/www/smarthcm/docs/erd/benefits.svg)
   - Entities: `benefit_plans`, `benefit_enrollments`, `benefit_claims`, `financial_wellness_programs`, `salary_advances`.
6. **Performance & Talent Succession**: [`docs/erd/performance.svg`](file:///c:/laragon/www/smarthcm/docs/erd/performance.svg)
   - Entities: `performance_cycles`, `performance_goals`, `performance_reviews`, `succession_plans`, `talent_pools`.
7. **Learning & Development**: [`docs/erd/learning.svg`](file:///c:/laragon/www/smarthcm/docs/erd/learning.svg)
   - Entities: `courses`, `course_contents`, `course_enrollments`, `certifications`, `training_sessions`.
8. **Workforce Analytics & Command Center**: [`docs/erd/workforce.svg`](file:///c:/laragon/www/smarthcm/docs/erd/workforce.svg)
   - Entities: `hcm_command_center_kpis`, `hcm_command_center_alerts`, `headcount_plans`, `workforce_scenarios`.
9. **Artificial Intelligence & Operations**: [`docs/erd/ai.svg`](file:///c:/laragon/www/smarthcm/docs/erd/ai.svg)
   - Entities: `hcm_ai_concierge_sessions`, `hcm_ai_gov_models`, `hcm_ai_gov_incidents`, `hcm_ai_eval_datasets`.
10. **Data Governance & Tenancy**: [`docs/erd/governance.svg`](file:///c:/laragon/www/smarthcm/docs/erd/governance.svg)
    - Entities: `tenants`, `hcm_gov_master_mappings`, `hcm_gov_lineage_nodes`, `hcm_gov_kpi_registries`.

---

## 4. Level 3: Full Enterprise Schema Vector Graph

- **File Path**: [`docs/erd/enterprise_hcm_full_erd.svg`](file:///c:/laragon/www/smarthcm/docs/erd/enterprise_hcm_full_erd.svg)
- **Format**: Scalable Vector Graphics (SVG), 450 KB.
- **Content**: Visualizes all 975 tables in a grid cluster layout, displaying primary keys, column counts, and domain color badges. Designed to be opened in any modern browser or vector tool (Figma, Illustrator, Inkscape) with infinite zoom capabilities without raster distortion.
