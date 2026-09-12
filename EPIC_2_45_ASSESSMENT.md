# EPIC 2.45 — Architecture Assessment & Reuse Matrix

## 1. Executive Summary

This assessment evaluates existing SmartHCM enterprise capabilities to prepare for **EPIC 2.45 — HCM Workforce Capacity, Productivity, Workload & Operational Workforce Optimization**.

The core objective of Epic 2.45 is to solve the fundamental enterprise workforce question:
> *"Given our workforce, workload, schedules, skills, availability and business demand, do we have sufficient operational capacity and how should we optimize it?"*

To adhere strictly to the **Zero Duplicate Engines Rule**, this assessment establishes clear cross-domain boundaries, identifies authoritative data owners, catalogs reusable components, and defines the exact extensions and new capabilities required.

---

## 2. Cross-Domain Inspection & Reuse Matrix

| Domain Area | Existing Capability | Current Owner | Reusable Component | Required Extension | New Capability Needed | Data Ownership | Cross-Domain Boundary | Duplicate Prevention |
|---|---|---|---|---|---|---|---|---|
| **Workforce Planning** (Epic 2.24 / 2.44) | Strategic plans, demand/supply plans, scenarios, cost plans, position budgets | `WorkforcePlanning` | `HcmWorkforcePlan`, `HcmWorkforceScenario`, `HcmWorkforceDemandPlan`, `HcmWorkforceSupplyPlan`, `HcmWorkforcePositionBudget` | Link capacity models to strategic plans; link capacity scenarios to `HcmWorkforceScenario` | Operational capacity forecasting, gap analysis, workload-to-FTE translation | `WorkforcePlanning` owns strategic workforce plan; `WorkforceCapacity` owns operational capacity models & calculations | Capacity feeds forward from strategic plans and provides operational feedback to next plan cycles | **Zero Duplicate Planning Engine**: Reuses `HcmWorkforceScenario` and plan period infrastructure |
| **Workforce Administration** (Epic 2.40) | Operational HR queues, calendars, rules, exceptions, lifecycle actions | `WorkforceAdmin` | HR queues, operational task dispatching, governance monitoring | Operational capacity exception alerts, action task integration | Capacity optimization actionable tasks dispatch | `WorkforceAdmin` owns operational task queues | Optimization actions create HR operational tasks if manual review needed | Reuses existing task and exception logging |
| **Org Design & Job Architecture** (Epic 2.43) | Job families, sub-families, job profiles, career tracks, job levels | `OrganizationDesign` | `JobFamily`, `JobSubFamily`, `JobProfile`, `JobProfileSkill`, `CareerTrack`, `CareerLevel` | Aggregate capacity, workload, and productivity by Job Family and Job Profile | Job-profile-level effort and standard capacity standards | `OrganizationDesign` owns job architecture definitions | Capacity models associate with Job Families and Profiles | Reuses job architecture taxonomy without modifying reference structures |
| **Core HR & Positions** | Employees, assignments, employment status, actual positions | `Employee` / `Organization` | `Employee`, `Position` | FTE calculation, contractual working hours lookup, vacancy identification | Active available headcount and FTE capacity aggregation | Core HR owns employees and positions | Capacity queries headcount and vacancies; never modifies employee records | **Zero Duplicate Employee / Position Master** |
| **Time & Attendance** | Worked hours, regular/overtime hours, shifts, attendance sessions, timesheets | `Attendance` | `AttendanceSession`, `Timesheet`, `ShiftDefinition`, `RosterAssignment`, `WorkCalendar` | Fetch actual worked hours, regular vs overtime breakdown, scheduled hours | Operational shrinkage calculation, actual vs scheduled variance | `Attendance` owns attendance events, timesheets, and overtime approval | Read-only aggregation of actual attendance and overtime | **Zero Duplicate Attendance Engine** |
| **Leave & Absence** | Leave requests, approvals, balances, absence records | `Attendance` / Leave module | `TimesheetEntry` (is_leave), `AttendanceSession` (status = on_leave), leave tables | Calculate leave-based capacity shrinkage and planned absence impact | Planned absence capacity reduction forecasts | Leave/Attendance owns leave requests and balances | Capacity queries approved leave days/hours; never approves or deducts leave | **Zero Duplicate Leave Engine** |
| **Scheduling & Rostering** | Shift schedules, rosters, patterns, calendar days | `Attendance` | `RosterAssignment`, `RosterPeriod`, `ShiftPattern`, `WorkCalendarDay` | Scheduled available capacity hours per shift and day | Shift capacity analysis, shift coverage shortage detection | `Attendance` owns rosters and shift assignments | Capacity queries schedule rosters to determine available hours; does not alter rosters | **Zero Duplicate Scheduling Engine** |
| **Skills & Competencies** | Skill inventory, employee skills, proficiency levels, skill gaps | `Career` | `CareerSkill`, `EmployeeSkill`, `CareerSkillGap` | Match required capacity by skill proficiency against qualified employee pool | Skill-based capacity gap calculation, reskilling potential identification | `Career` owns skill catalog and employee skill proficiencies | Capacity analyzes qualified vs required skills; does not assign skills | **Zero Duplicate Skills Master** |
| **Learning & Development** | Courses, training programs, certifications, completions | `Learning` | `LearningCourse`, `LearningRequirement` | Map identified reskilling needs to available learning courses | Reskilling capacity pipeline estimation | `Learning` owns training execution | Capacity creates planning recommendations for learning; does not enroll users | Reuses learning catalog |
| **Recruitment / ATS** | Requisitions, candidate pipelines, job openings, target start dates | `Recruitment` | `HcmRecruitmentRequisition`, `HcmRecruitmentApplication` | Calculate expected capacity recovery based on open requisitions and offer stage | Recruitment pipeline capacity recovery forecasting | `Recruitment` owns job requisitions and candidate hiring pipeline | Capacity reads pipeline to estimate incoming FTE; does not open requisitions without approval | Reuses requisition data |
| **Compensation & Finance** | Salary bands, labor cost budgets, actual labor costs | `Compensation` / `Finance` | `CompensationBand`, `HcmWorkforcePositionBudget` | Cost estimation for capacity options (hire vs contractor vs overtime vs reskill) | Capacity alternative cost comparison (make/buy/borrow) | Compensation & Finance own pay bands and financial budgets | Capacity references pay benchmarks for financial impact analysis | **Zero Duplicate Finance Engine** |
| **Workflow Engine** | Approvals, transitions, task routing | `Workflow` | Workflow definitions, instances, steps | Route capacity optimization recommendations for multi-step human review | Workflow-driven personnel action / requisition triggers | `Workflow` owns process execution | Capacity initiates review workflows; engine handles states and approvals | Reuses platform workflow engine |
| **Rules Engine** | Rule evaluation, conditions, action triggers | `Rules` | Rule sets, rule conditions | Configurable alert thresholds (e.g. utilization > 110%, skill deficit > 20%) | Capacity threshold policy evaluation | `Rules` owns business rule configuration | Capacity triggers rule evaluation for automated alerts | Reuses platform rules engine |
| **Notification Platform** | In-app alerts, email notifications, webhooks | `Communication` / Platform | Notification channels and templates | Dispatch alerts for critical capacity shortages, high overtime risk, bottlenecks | Multi-channel capacity warnings | Platform owns notification routing | Capacity sends notification payloads | Reuses platform notifications |

---

## 3. Clear Ownership & Architectural Boundaries

```text
       ┌────────────────────────────────────────────────────────┐
       │             Strategic Workforce Planning               │
       │                 (Epic 2.24 & Epic 2.44)                 │
       └──────────────────────────┬─────────────────────────────┘
                                  │ Strategic Goals & Headcount Plans
                                  ▼
       ┌────────────────────────────────────────────────────────┐
       │                 Workforce Capacity                     │
       │                    (Epic 2.45)                         │
       │                                                        │
       │  • Workload Definitions & Drivers                      │
       │  • Capacity Models & Assumptions                       │
       │  • Required Capacity (Workload × Effort)               │
       │  • Available Capacity (Contracted - Shrinkage)         │
       │  • Capacity Gap & Surplus Calculation                  │
       │  • Utilization Analysis                                │
       │  • Contextual Productivity Metrics                     │
       │  • Skill-Based Capacity & Reskilling Potential         │
       │  • Make/Buy/Borrow Capacity Scenarios                  │
       │  • Advisory AI Recommendations (is_advisory_only: true)│
       └─────┬────────────────────┬───────────────────────┬─────┘
             │                    │                       │
      Feeds  │             Queries│                Triggers│
      Actuals│             Master │                 Review │
             ▼                    ▼                       ▼
┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
│ Attendance/Leave │    │ Core HR, Career  │    │ Workflow, Rules, │
│ Overtime, Hours  │    │ Skills, Profiles │    │ Notifications    │
└──────────────────┘    └──────────────────┘    └──────────────────┘
```

### Strict Non-Negotiable Boundaries:
1. **Zero Autonomous Employment Actions**: The advisory AI and optimization engine must NEVER automatically hire, terminate, transfer, adjust pay, or alter schedules. Output is strictly advisory (`is_advisory_only: true`).
2. **No Universal "0-100" Employee Productivity Score**: Productivity metrics must be contextual and domain-specific (e.g., tickets resolved per productive hour, transactions processed per FTE). Employee-level metrics are strictly restricted behind granular permissions.
3. **Medical & Absence Privacy**: Absence data is aggregated as capacity shrinkage hours; medical diagnoses and sensitive leave reasons are never exposed in capacity dashboards.
4. **Tenant Isolation**: All database tables, calculation queries, scenarios, and cache keys must be strictly scoped by `tenant_id`.

---

## 4. Assessment Outcome & Readiness

The SmartHCM application has mature foundations in Workforce Planning, Time & Attendance, Recruitment, Career/Skills, and Job Architecture. 
Epic 2.45 will seamlessly complete the operational workforce optimization loop without duplicating any existing platform service.
