# EPIC 2.52 — Architecture Assessment & Implementation Blueprint
## HCM Workforce Intelligence Command Center, Executive People Analytics, Cross-Domain Workforce Cockpit & Strategic Decision Support

**Domain Namespace**: `App\Domains\WorkforceIntelligence`  
**Database Tables Prefix**: `hcm_command_center_*`  
**Service Role**: Cross-Domain Intelligence, Aggregation, Analytics & Decision Cockpit (Read-Heavy & Orchestration Layer)

---

### 1. Executive Summary & Purpose
EPIC 2.52 provides an enterprise-grade command center that consolidates, harmonizes, and contextualizes workforce intelligence across all upstream HCM modules:
- Core HR & Organization (`App\Domains\Employee`, `App\Domains\Organization`)
- Workforce Planning & Capacity (`App\Domains\WorkforcePlanning` - Epic 2.48)
- Workforce Cost & Financial Planning (`App\Domains\WorkforceCost` - Epic 2.49)
- Workforce Productivity & Performance-to-Cost (`App\Domains\WorkforceProductivity` - Epic 2.50)
- Workforce Optimization & Decision Intelligence (`App\Domains\WorkforceOptimization` - Epic 2.51)
- Time, Attendance, Absence, and Scheduling

The Command Center is strictly an **aggregation and cockpit layer** (`ANALYTICS`, `INTELLIGENCE`, `DECISION SUPPORT`). It does NOT manage transactional records (no source payroll, leaves, roster editing, or disciplinary actions). Instead, it surfaces unified executive metrics, composite health scores, cross-domain root-cause explanations (`OBSERVED`, `CORRELATED`, `INFERRED`), multi-domain risk & alert prioritization, centralized decision queues, and natural-language queries backed by strict RBAC and security guardrails.

---

### 2. Core Architectural Pillars

#### 2.1 Cross-Domain Data Bindings & Read-Model Aggregation
- Pulls from `hcm_workforce_plans`, `hcm_workforce_cost_snapshots`, `hcm_productivity_measurements`, `hcm_workforce_optimization_recommendations`, `employees`, and organizational structures.
- Provides materialized snapshot tables (`hcm_command_center_snapshots`, `hcm_command_center_kpi_values`) with explicit provenance and freshness timestamps (`freshness_ttl_seconds`, `source_system`, `source_extracted_at`).

#### 2.2 Semantic Metric & KPI Governance
- Single Source of Truth for metric definitions:
  - Metric code, human-readable name, formula string, aggregation method, target direction (`HIGHER_IS_BETTER`, `LOWER_IS_BETTER`, `TARGET_RANGE`).
  - Versioned definitions (`hcm_command_center_kpi_versions`) guaranteeing historical reproducibility of executive board reports.
  - Security classifications: `PUBLIC`, `INTERNAL`, `CONFIDENTIAL`, `RESTRICTED`.

#### 2.3 Transparent Composite Workforce Health Index (0–100)
- Calculated using weighted dimensions:
  1. Capacity & Staffing Health (Weight: 20%)
  2. Productivity & Efficiency (Weight: 25%)
  3. Cost & Budget Adherence (Weight: 20%)
  4. Workforce Stability & Retention (Weight: 15%)
  5. Skills & Capability Readiness (Weight: 10%)
  6. Operational Compliance & Workload Balance (Weight: 10%)
- Every score is accompanied by component scores, raw inputs, formula breakdown, and confidence scoring.

#### 2.4 Cross-Domain Root-Cause Explanations
- Three-tier attribution:
  - `OBSERVED`: Direct factual data points (e.g. Overtime jumped 34% in Department X).
  - `CORRELATED`: Statistically correlated movements (e.g. Overtime increase coincided with a 18% spike in unscheduled sick leave).
  - `INFERRED`: Rule/algorithm-driven root-cause hypothesis (e.g. Capacity deficit in Core Assembly is driving overtime surge due to 3 unfilled technician vacancies).

#### 2.5 Consolidated Decision Queue & Alert Center
- Aggregates actionable pending approvals:
  - Optimization recommendations (`App\Domains\WorkforceOptimization`)
  - Workforce planning reconciliations (`App\Domains\WorkforcePlanning`)
  - High-risk overtime/cost overruns (`App\Domains\WorkforceCost`)
- Deep links and routes actions back to the authoritative domain modules with unified status tracking (`PENDING`, `IN_REVIEW`, `APPROVED`, `REJECTED`, `EXPIRED`).

#### 2.6 Guardrailed AI-Assisted Workforce Queries
- Semantic query parser that matches natural language questions against governed KPIs, dimensions, and organizational scope.
- **Strict Ethical & Safety Constraints**:
  - Non-punitive: Never recommends terminating specific named employees.
  - No protected health/medical inferences.
  - Mandatory source citation and timeframe disclosure.
  - Audit logging (`hcm_command_center_audits`) of all natural-language prompts and returned metrics.

#### 2.7 Persona-Aware Cockpits & Customization
- Executive Cockpit (Strategic high-level KPIs, health index, top enterprise risks).
- Finance Cockpit (Cost per FTE, budget variance, overtime premium leakage, ROI).
- HR / People Analytics Cockpit (Retention, absenteeism, vacancy aging, skill SPOFs).
- Operations / Line Manager Cockpit (Department headcount, capacity utilization, shift coverage, team overtime).

---

### 3. Database Schema Overview (`hcm_command_center_*`)

1. `hcm_command_center_kpis`: Governed KPI definitions.
2. `hcm_command_center_kpi_versions`: Versioned formulas, weights, and logic.
3. `hcm_command_center_kpi_values`: Materialized/cached periodic metric values by department, tenant, period.
4. `hcm_command_center_snapshots`: Executive workforce intelligence rollups.
5. `hcm_command_center_health_indices`: Composite health scores and dimension breakdowns.
6. `hcm_command_center_risks`: Cross-domain aggregated risks.
7. `hcm_command_center_alerts`: Prioritized operational & strategic alerts.
8. `hcm_command_center_decision_items`: Cross-domain decision/approval queue.
9. `hcm_command_center_dashboards`: Configurable dashboard containers.
10. `hcm_command_center_widgets`: Persona-based widget definitions and placements.
11. `hcm_command_center_saved_views`: Saved filters, periods, and organizational scopes.
12. `hcm_command_center_user_preferences`: User pinning, order, and view customization.
13. `hcm_command_center_audits`: Immutable compliance logs for AI queries, metric modifications, and exports.

---

### 4. Implementation Phasing
- **Phase 1**: Architecture Assessment Document (Completed)
- **Phase 2**: Schema Migration (`hcm_command_center_*`)
- **Phase 3**: Enums, Value Objects & DTOs
- **Phase 4**: Eloquent Models & Service Contracts
- **Phase 5**: Core Domain Services
- **Phase 6**: Artisan CLI Commands & Queued Jobs
- **Phase 7**: Controllers, API & Web Routes, Blade Views
- **Phase 8**: Feature & Unit Test Suite
- **Phase 9**: Documentation & Final Verification
