# EPIC 2.46 — Architecture Assessment & Reuse Matrix

## 1. Executive Summary

This pre-implementation assessment evaluates the current SmartHCM architecture to prepare for **EPIC 2.46 — HCM Workforce Scheduling, Shift Management, Rostering, Skills-Based Scheduling & Real-Time Workforce Allocation**.

Epic 2.46 builds directly upon:
- **Epic 2.18** (*Time, Attendance, Shifts, Rostering & Workforce Scheduling*) as the scheduling foundation.
- **Epic 2.45** (*Workforce Capacity, Productivity, Workload & Operational Workforce Optimization*) as the capacity demand and coverage requirement layer.

The central mission is to convert workforce demand and capacity requirements into executable workforce schedules while continuously respecting employee availability, employment rules, working hours, skills, qualifications, certifications, shift rules, labor constraints, leave, attendance, holidays, rest periods, overtime, and location requirements.

---

## 2. Cross-Domain Inspection & Component Reuse Matrix

| Domain Area | Existing Platform Capability | Current Owner | Reusable Component | Required Extension for Epic 2.46 | New Capability Needed | Data Ownership | Cross-Domain Boundary | Duplicate Prevention Rule |
|---|---|---|---|---|---|---|---|---|
| **Epic 2.18 (Rostering & Shifts)** | Shift definitions, shift patterns, roster periods, roster assignments, roster conflicts | `Attendance` | `ShiftDefinition`, `ShiftPattern`, `RosterPeriod`, `RosterAssignment`, `RosterConflict`, `RosterConflictEngine` | Add schedule versioning, publishing workflow, schedule locking, cost differential | Schedule versions, schedule publication status, schedule snapshots | `Scheduling` owns planned assignments; `Attendance` owns actuals | Planned schedule is passed to attendance to evaluate punctuality/absence | **Zero Duplicate Shift/Roster Master**: Extends existing `shift_definitions` and `roster_periods` / `roster_assignments` |
| **Epic 2.45 (Workforce Capacity)** | Capacity models, workload drivers, required effort hours, capacity gaps | `WorkforceCapacity` / `WorkforcePlanning` | `HcmCapacityModel`, `HcmWorkloadDefinition`, `HcmCapacityCalculation` | Ingest required capacity/FTE into hourly and shift coverage requirements | Demand-driven coverage requirements (`hcm_schedule_coverage_requirements`) | `WorkforceCapacity` owns capacity calculations | Scheduling ingests required staffing per shift/interval to calculate coverage gap | **Zero Duplicate Capacity Engine**: Reads requirements from Epic 2.45 |
| **Core HR & Positions** | Employees, job titles, positions, worker types, locations, departments | `Employee` / `Organization` | `Employee`, `Position`, `WorkLocation`, `Department` | Reference required positions on shift coverage lines | Position-based shift requirements, employee location eligibility check | Core HR owns employees, positions, departments, locations | Scheduling references employee IDs and positions; does not create employee masters | **Zero Duplicate Employee / Position Master** |
| **Skills & Competencies** | Skills inventory, employee skill proficiencies, verification status | `Career` | `CareerSkill`, `EmployeeSkill`, `CareerSkillLevel` | Evaluate required skill & minimum proficiency level for shift eligibility | Skill-based shift eligibility and skill coverage scoring | `Career` owns skills and verified proficiency | Scheduling checks if employee has required verified skill before assignment | **Zero Duplicate Skills Master** |
| **Compliance & Learning** | Training records, mandatory course completions, certifications | `Learning` / `Compliance` | `LearningRequirement`, `LearningCourse`, compliance documents | Filter shift assignments based on valid/active certifications & licenses | Certification expiration check during schedule validation | Learning/Compliance owns certifications | Scheduling validates certificate validity; does not grant licenses | Reuses existing learning/compliance catalog |
| **Leave & Absence** | Leave applications, approved leaves, balances | `Attendance` / Leave module | `leave_applications`, `TimesheetEntry` (is_leave), `AttendanceSession` (status = on_leave) | Automatic conflict detection and blocking when employee has approved leave | Real-time leave conflict prevention in schedule builder and swap engine | Leave module owns leave records | Leave approval triggers schedule conflict check and alerts manager | **Zero Duplicate Leave Engine** |
| **Time & Attendance** | Actual clock-in/out, worked hours, regular vs overtime, attendance exceptions | `Attendance` | `AttendanceSession`, `Timesheet`, `AttendanceEvent`, `AttendanceException` | Compare scheduled shift vs actual attendance (punctuality, no-shows, schedule deviations) | Real-time operational coverage dashboard (Scheduled vs Present vs Available) | `Attendance` owns clock transactions and worked hours | Scheduling compares planned shifts with actual sessions; does not duplicate punch logs | **Zero Duplicate Attendance Engine** |
| **Compensation & Payroll** | Compensation bands, salary structures, shift premiums | `Compensation` / `Payroll` | `CompensationBand`, shift differential percentages | Compute estimated schedule labor cost and shift premium differential | Schedule cost estimation and overtime cost risk projection | Compensation/Payroll owns pay rates and salary calculation | Scheduling calculates estimated cost for planning; does not generate payroll | **Zero Duplicate Payroll Engine** |
| **Workflow Engine** | Workflow definitions, approvals, stage transitions | `Workflow` | Platform workflow instance and transition runner | Shift swap approvals, schedule publication approval, emergency overrides | Multi-level approval steps for schedule changes and swaps | `Workflow` owns process state | Scheduling triggers workflow requests; engine handles transitions | Reuses platform workflow engine |
| **Rules Engine** | Rule definitions, conditions, validation policies | `Rules` / `RosterConflictEngine` | `RosterConflictEngine`, rule condition evaluation | Support hard constraints (blocking) vs soft constraints (warning/penalty) | Configurable constraint evaluation (rest periods, max hours, consecutive shifts) | `Rules` owns business rule configuration | Scheduling evaluates rules during validation and optimization | Reuses platform rules engine |
| **Notification Platform** | In-app alerts, email notifications, push notifications | `Communication` / Platform | Notification dispatching system | Dispatch alerts: schedule published, shift swap requested, open shift available, no-show alert | Multi-channel scheduling notifications | Platform owns notification routing | Scheduling generates event payloads | Reuses platform notification platform |

---

## 3. Strict Architectural Boundaries

```text
                               ┌─────────────────────────────┐
                               │   Epic 2.45 Capacity        │
                               │   & Business Demand         │
                               └──────────────┬──────────────┘
                                              │ Required Capacity / Staffing
                                              ▼
┌───────────────────────────┐  Coverage Lines ┌─────────────────────────────┐
│  Core HR & Position       ├────────────────►│   Workforce Scheduling      │
│  Departments & Locations  │                 │   (Epic 2.46)               │
└───────────────────────────┘                 │                             │
┌───────────────────────────┐  Skill Match    │  • Schedule Periods         │
│  Career Skills            ├────────────────►│  • Shift Definitions        │
│  & Learning Certificates  │                 │  • Shift Patterns           │
└───────────────────────────┘                 │  • Coverage Heatmaps        │
┌───────────────────────────┐  Hard Block     │  • Availability & Prefs     │
│  Approved Leave           ├────────────────►│  • Validation Engine        │
│  & Absence Records        │                 │  • Optimization Engine      │
└───────────────────────────┘                 │  • Shift Swaps & Open Shifts│
                                              │  • Publishing & Locks       │
                                              └──────────────┬──────────────┘
                                                             │ Planned Shifts
                                                             ▼
                                              ┌─────────────────────────────┐
                                              │   Time & Attendance         │
                                              │   (Epic 2.18)               │
                                              │                             │
                                              │  • Actual Clock In/Out      │
                                              │  • Worked Hours / Overtime  │
                                              │  • No-Show & Deviation      │
                                              └──────────────┬──────────────┘
                                                             │ Actuals
                                                             ▼
                                              ┌─────────────────────────────┐
                                              │   Real-Time Operational     │
                                              │   Coverage & Optimization   │
                                              └─────────────────────────────┘
```

### Non-Negotiable Governance Principles:
1. **Advisory AI Only**:
   - The schedule optimization and AI recommendation services are strictly advisory (`is_advisory_only: true`).
   - AI must NEVER make adverse employment decisions, penalize employees, terminate workers, or override mandatory compliance rules.
2. **Schedule Quality vs Employee Performance**:
   - The Schedule Quality Score reflects how well a proposed schedule satisfies coverage, constraints, and employee preferences. It must NEVER be converted into an individual employee performance evaluation score.
3. **Employee Privacy & Data Minimization**:
   - Employees may see coworkers on the same shift if permitted, but must NEVER see other employees' private availability reasons, medical absence justifications, or wage information.
4. **Tenant Isolation**:
   - Every database table, assignment, swap request, availability record, and queue job must strictly enforce `tenant_id` scoping.

---

## 4. Assessment Conclusion & Readiness

The foundation provided by Epic 2.18 (`shift_definitions`, `roster_assignments`, `RosterConflictEngine`) and Epic 2.45 (Capacity models, workload drivers) is robust. 
Epic 2.46 extends these existing structures into a closed-loop workforce scheduling engine without duplicating any platform engine or core master data.
