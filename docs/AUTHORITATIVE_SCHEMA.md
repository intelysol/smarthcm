# Enterprise HCM — Authoritative Database Schema Architecture

> **Baseline Source of Truth**: `database/schema/enterprise_hcm_full_schema.sql`  
> **Database Targets**: MySQL 8.0+ / MariaDB 10.6+ (`ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`)

---

## 1. Architectural Principles

From this point forward, the authoritative consolidated SQL schema represents the primary foundation of the Enterprise HCM data tier:

$$\text{AUTHORITATIVE SQL SCHEMA} \longrightarrow \text{DATABASE} \longrightarrow \text{LARAVEL MIGRATIONS} \longrightarrow \text{ELOQUENT MODELS} \longrightarrow \text{APIs/APPLICATION}$$

All layers must remain strictly synchronized. No individual migration or model may violate the architectural boundaries established herein.

---

## 2. Identity Standards & Conventions

### 2.1 Users & Authentication (`users`)
- **Primary Key**: `users.id` MUST be `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY`.
- **Foreign Key Convention**: Every column in any table across the entire enterprise that references a user (whether as owner, auditor, reviewer, approver, or creator) MUST be `BIGINT UNSIGNED` and reference `users(id)`.
- **Standard User Reference Columns**:
  ```sql
  `user_id` BIGINT UNSIGNED,
  `created_by` BIGINT UNSIGNED,
  `updated_by` BIGINT UNSIGNED,
  `deleted_by` BIGINT UNSIGNED,
  `reviewed_by` BIGINT UNSIGNED,
  `approved_by` BIGINT UNSIGNED,
  `rejected_by` BIGINT UNSIGNED,
  `requested_by` BIGINT UNSIGNED,
  `uploaded_by` BIGINT UNSIGNED,
  `verified_by` BIGINT UNSIGNED,
  `actor_id` BIGINT UNSIGNED,
  `assigned_by` BIGINT UNSIGNED,
  `assigned_to_user_id` BIGINT UNSIGNED,
  `actioned_by_user_id` BIGINT UNSIGNED,
  `acknowledged_by_user_id` BIGINT UNSIGNED
  ```
- **Constraint Rule**: All user references must enforce either `ON DELETE CASCADE` (for private user assets) or `ON DELETE SET NULL` (for audit and governance records).

### 2.2 Multi-Tenant Isolation (`tenants`)
- **Primary Key**: `tenants.id` is `CHAR(36)` / `UUID`.
- **Tenant Foreign Key**: Every tenant-scoped table contains `tenant_id CHAR(36) NOT NULL` (or nullable where explicitly platform-shared).
- **Isolation Enforcement**: Tenant ownership is enforced at the database level with `FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE`.
- **Tenant Scope Index**: Every tenant-owned table features a composite index on `(`tenant_id`, ...)` or an individual index on `tenant_id` to guarantee tenant partition query efficiency.

### 2.3 Employees (`employees`)
- **Primary Key**: `employees.id` is `CHAR(36)` / `UUID`.
- **Clear Distinction from User**: An `employee` represents a personnel record within a tenant organization; a `user` represents an authentication identity. References to employees (`employee_id`, `subject_employee_id`, `reporting_manager_id`) MUST remain `CHAR(36)` / `UUID` and must never be merged with user IDs.

### 2.4 Organization Entities (`companies`, `departments`, `positions`)
- Primary keys for business organizational entities (`companies`, `departments`, `positions`, `designations`) are strictly `UUID` (`CHAR(36)`).

---

## 3. Database Engine & Character Set Standards

All tables must be generated with:
```sql
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Identifier & Key Length Rules
1. **Identifier Maximum Length**: 64 characters (MySQL limit). All auto-generated index and foreign key names exceeding 64 characters must be explicitly shortened.
2. **Composite Key Length Limit**: In InnoDB with `utf8mb4`, maximum index prefix key length is 3,072 bytes ($768$ characters). Composite indexes must never combine unconstrained `VARCHAR(255)` columns that exceed 768 total characters.

---

## 4. Multi-Tenant Data Domain Breakdown

The reconciled schema comprises **985 tables** and **2,180 foreign keys** organized across 28 distinct functional domains:

1. **Identity & Security** (51 tables): Users, roles, permissions, MFA methods, MFA recovery codes, trusted devices, user devices, user sessions, login attempts, password histories, OAuth clients, API tokens, security events.
2. **Tenancy & Administration** (34 tables): Tenants, tenant configurations, domains, branding, lifecycle audits.
3. **Organization & Hierarchy** (56 tables): Companies, business units, departments, sections, teams, positions, job families.
4. **Employee Core** (68 tables): Personnel records, personal data, bank accounts, emergency contacts, documents, timeline events.
5. **Time & Attendance** (48 tables): Shifts, rosters, biometric logs, attendance policies, regularization requests.
6. **Leave Management** (26 tables): Leave policies, types, balances, allocations, leave applications.
7. **Payroll Engine** (64 tables): Calculation lines, payslips, payment batches, entries, formula rules, accounting exports.
8. **Compensation & Planning** (36 tables): Salary structures, compensation bands, market benchmarks, total rewards.
9. **Benefits & Wellness** (42 tables): Health plans, benefit enrollments, claims, financial wellness programs.
10. **Expense & Travel** (38 tables): Expense categories, policies, claims, travel requests, advance settlements.
11. **Talent Acquisition** (52 tables): Requisitions, job postings, candidate pipelines, interview evaluations, background checks.
12. **Onboarding & Offboarding** (38 tables): Onboarding checklists, template versions, policy acknowledgments, exit interviews.
13. **Learning & Development** (44 tables): Course catalogs, cohorts, skills matrices, certifications, learning paths.
14. **Performance & Succession** (58 tables): Appraisal cycles, goals, 360 feedback, calibration sessions, 9-box grids, succession candidate pools.
15. **Employee Relations** (34 tables): Workplace conduct cases, investigation steps, evidence, statements, appeals, corrective actions.
16. **Document Management** (32 tables): Folder hierarchies, templates, electronic signatures, document links, versioning.
17. **HR Service Delivery** (40 tables): Ticket queues, SLAs, service catalogs, announcements, knowledge bases.
18. **Workforce Analytics & Planning** (46 tables): Headcount plans, capacity forecasting, scenario modeling, metrics.
19. **Command Center & Intelligence** (28 tables): Executive dashboards, KPI values, health indices, risk indicators, audits.
20. **Data Governance & Metadata** (36 tables): Data dictionaries, entity catalogs, lineage edges, master data mappings.
21. **Responsible AI Governance** (32 tables): AI use case registries, model performance, fairness checks, kill switches, audit trails.
22. **AI Operations & Concierge** (26 tables): Conversational sessions, intent routing, action recommendations, telemetry.
23. **Global Mobility** (24 tables): Expatriate assignments, visa management, tax equalization, relocation tasks.
24. **Health & Safety** (28 tables): Incident investigations, OSHA compliance, workplace accommodations, medical fitness records.
25. **Absence & Return to Work** (22 tables): Extended medical leaves, return-to-work plans, modified duty assignments.
26. **Workflow Engine** (26 tables): Dynamic BPMN workflows, approval steps, delegations, conditional transitions.
27. **Integration Hub & Public APIs** (34 tables): Webhook subscriptions, API rate limits, product scopes, connector registry.
28. **Platform Operations** (22 tables): Audit logs, migrations, background queues, failed jobs, cache locks, release registries.

