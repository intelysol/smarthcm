# Enterprise Experience Architecture — Workspace Map

## 1. Executive Summary

Epic 2.69 replaces the legacy monolithic single-shell layout with **7 discrete, role-aware application workspaces**. Each workspace targets a specific organizational audience with a dedicated layout shell, curated navigation, and operational or personal dashboards.

```text
                           ENTERPRISE WORKSPACE MAP
                                      │
   ┌───────────────────┬──────────────┴─────────────┬──────────────────┐
   │                   │                            │                  │
Platform Admin    Tenant Admin                  HR Operations      My Team (Manager)
(/platform/*)     (/admin/*)                    (/hr/*)            (/manager/*)
   │                   │                            │                  │
   └───────────────────┴──────────────┬─────────────┴──────────────────┘
                                      │
                 ┌────────────────────┴────────────────────┐
                 │                                         │
        Employee Workplace                        Workforce Intelligence (Exec)
        (/employee/*, /portal/*)                  (/executive/*)
                 │                                         │
                 └────────────────────┬────────────────────┘
                                      │
                              System Operations
                              (/operations/*)
```

---

## 2. Workspace Directory & Target Audience

| Workspace | Route Prefix | Target Audience | Primary Focus | Layout Shell |
|:---|:---|:---|:---|:---|
| **Platform Control Center** | `/platform/*` | Platform Super Admin, DevOps, Billing Admin | Global SaaS administration, multi-tenant provisioning, licensing, platform health | `shells.platform` (Dark Slate) |
| **Tenant Administration** | `/admin/*` | Tenant Owner, Org Admin, IT Administrator | Organization structure, departments, positions, roles, workflows, org settings | `shells.tenant` (Slate/Indigo) |
| **HR Operations** | `/hr/*` | HR Director, HR Specialist, Payroll & Talent Admins | Workforce administration, recruitment, payroll, leave reviews, benefits, employee cases | `shells.hr` (Corporate Navy) |
| **Manager Workspace** | `/manager/*` | People Managers, Team Leads, Supervisors | Direct report roster, attendance, leave approvals, 1-on-1s, team reviews | `shells.manager` (Navy Dark) |
| **Employee Self-Service** | `/employee/*`, `/portal/*` | All Active Employees & Staff | Daily work shift, attendance punch, leave balances, payslips, personal profile, requests | `shells.employee` (Clean Card UX) |
| **Executive Intelligence** | `/executive/*` | C-Suite, Board, VP HR, Managing Directors | Strategic workforce analytics, headcount trajectory, total compensation cost, capacity | `shells.executive` (Indigo/Gold) |
| **System Operations** | `/operations/*` | DevOps, Platform Operations, IT Operations | Queue monitoring, Redis health, Horizon workers, biometric devices, audit telemetry | `shells.operations` (Zinc Dark) |

---

## 3. Separation of Concerns & UX Rules

1. **Administrators manage systems and people**:
   - Platform & Tenant Admin emphasis: Configuration, provisioning, permissions, audit trails, and global policies.
2. **Managers manage team execution**:
   - Manager emphasis: Team attendance, shifts, pending approvals, team performance, and team capacity.
3. **Employees manage their own work and personal records**:
   - Employee emphasis: Today's schedule, clock in/out, personal leave balances, payslips, and HR service requests.
   - **Zero Administrative Clutter**: Employees are never exposed to tenant provisioning, database configurations, or platform queues.
4. **Executives monitor strategic intelligence**:
   - Executive emphasis: High-level KPI aggregates, workforce ROI, retention trends, and scenario modeling.
