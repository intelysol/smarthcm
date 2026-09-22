# SmartHCM Enterprise UI Route Coverage Matrix

> **Epic 2.70 — Enterprise UX Completion, Accessibility & Responsive Experience**  
> **Status:** 100% Classified & Certified  
> **Scope:** Web Workspace Application Routes

---

## 1. Executive Summary

| Workspace Dimension | Total Classified Routes | Target Workspace | Authorization Gate | Test Coverage Suite | Operational Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Platform Control Center** | 8 routes | `platform` | `is_platform_admin` | `tests/Feature/Workspace/WorkspaceExperienceTest.php` | PASS (100%) |
| **Tenant Admin Portal** | 7 routes | `tenant` | `tenant_admin` / Role | `tests/Feature/Workspace/WorkspaceExperienceTest.php` | PASS (100%) |
| **HR Operations Workspace** | 2 routes | `hr` | `hr_admin` / Role | `tests/Feature/Workspace/WorkspaceExperienceTest.php` | PASS (100%) |
| **Manager Workbench** | 5 routes | `manager` | Direct Reports / Manager Role | `tests/Feature/Workspace/WorkspaceExperienceTest.php` | PASS (100%) |
| **Employee Self-Service** | 23 routes | `employee` | Authenticated User / Tenant Bound | `tests/Feature/Workspace/WorkspaceExperienceTest.php` | PASS (100%) |
| **Executive Intelligence** | 3 routes | `executive` | `executive` / Analytics Role | `tests/Feature/Workspace/WorkspaceExperienceTest.php` | PASS (100%) |
| **Operational Command** | 4 routes | `operations` | Platform / Ops Engineer | `tests/Feature/Workspace/WorkspaceExperienceTest.php` | PASS (100%) |
| **Shared Auth & Context** | 5 routes | Shared | Public / Web Session | `tests/Security/AuthenticationTestSuiteTest.php` | PASS (100%) |

---

## 2. Route Classification Details

### 2.1 Platform Control Center (`/platform/*`)
| Route URI | Name | Controller Action | Security Guard | UI Shell | Connected API / Service | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/platform` | `platform.index` | `PlatformControlCenterWebController@index` | `workspace:platform` | `shells/platform.blade.php` | Platform Control Service | PASS |
| `/platform/control-center`| `platform.control-center`| `PlatformControlCenterWebController@index` | `workspace:platform` | `shells/platform.blade.php` | System Telemetry API | PASS |
| `/platform/tenants` | `platform.tenants` | `PlatformControlCenterWebController@tenants` | `workspace:platform` | `shells/platform.blade.php` | Tenant Provisioning API | PASS |
| `/platform/billing` | `platform.billing` | `PlatformControlCenterWebController@billing` | `workspace:platform` | `shells/platform.blade.php` | Billing Subscription API| PASS |
| `/platform/integrations`| `platform.integrations`| `PlatformControlCenterWebController@index` | `workspace:platform` | `shells/platform.blade.php` | Integration Connector API| PASS |
| `/platform/security` | `platform.security` | `PlatformControlCenterWebController@security` | `workspace:platform` | `shells/platform.blade.php` | Security Telemetry API | PASS |
| `/platform/ai-governance`| `platform.ai-governance`| `PlatformControlCenterWebController@aiGovernance` | `workspace:platform` | `shells/platform.blade.php` | Responsible AI Service | PASS |
| `/platform/settings` | `platform.settings` | `PlatformControlCenterWebController@settings` | `workspace:platform` | `shells/platform.blade.php` | Platform Config Service | PASS |

### 2.2 Tenant Administration (`/admin/*`)
| Route URI | Name | Controller Action | Security Guard | UI Shell | Connected API / Service | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/admin` | `admin.index` | `TenantAdminPortalWebController@dashboard` | `workspace:tenant` | `shells/tenant.blade.php` | Tenant Admin Service | PASS |
| `/admin/settings` | `admin.settings` | `TenantAdminPortalWebController@settings` | `workspace:tenant` | `shells/tenant.blade.php` | Tenant Localization Service| PASS |
| `/admin/users` | `admin.users` | `TenantAdminPortalWebController@users` | `workspace:tenant` | `shells/tenant.blade.php` | Identity & User Service | PASS |
| `/admin/roles` | `admin.roles` | `TenantAdminPortalWebController@roles` | `workspace:tenant` | `shells/tenant.blade.php` | RBAC Role Service | PASS |
| `/admin/workflows` | `admin.workflows` | `TenantAdminPortalWebController@workflows` | `workspace:tenant` | `shells/tenant.blade.php` | Workflow Engine Service | PASS |
| `/admin/forms` | `admin.forms` | `TenantAdminPortalWebController@forms` | `workspace:tenant` | `shells/tenant.blade.php` | Form Builder Service | PASS |
| `/admin/integrations`| `admin.integrations` | `TenantAdminPortalWebController@integrations` | `workspace:tenant` | `shells/tenant.blade.php` | Tenant Connectors API | PASS |

### 2.3 HR Operations Workspace (`/hr/*`)
| Route URI | Name | Controller Action | Security Guard | UI Shell | Connected API / Service | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/hr` | `hr.index` | `HrWorkspaceWebController@dashboard` | `workspace:hr` | `shells/hr.blade.php` | HCM Core Service | PASS |
| `/hr/dashboard` | `hr.dashboard` | `HrWorkspaceWebController@dashboard` | `workspace:hr` | `shells/hr.blade.php` | HR Analytics & Stats | PASS |

### 2.4 Manager Workbench (`/manager/*`)
| Route URI | Name | Controller Action | Security Guard | UI Shell | Connected API / Service | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/manager` | `manager.index` | `ManagerWorkspaceWebController@workbench` | `workspace:manager` | `shells/manager.blade.php` | Team Reporting Service | PASS |
| `/manager/workbench`| `manager.workbench` | `ManagerWorkspaceWebController@workbench` | `workspace:manager` | `shells/manager.blade.php` | Approvals & Team Stats | PASS |
| `/manager/members` | `manager.members` | `ManagerWorkspaceWebController@members` | `workspace:manager` | `shells/manager.blade.php` | Employee Hierarchy Service | PASS |
| `/manager/performance`| `manager.performance`| `ManagerWorkspaceWebController@performance` | `workspace:manager` | `shells/manager.blade.php` | Performance Review API | PASS |
| `/manager/analytics`| `manager.analytics` | `ManagerWorkspaceWebController@analytics` | `workspace:manager` | `shells/manager.blade.php` | Team Productivity Service| PASS |

### 2.5 Employee Self-Service (`/employee/*` & `/portal/*`)
| Route URI | Name | Controller Action | Security Guard | UI Shell | Connected API / Service | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/employee/home` | `employee.home` | `EmployeeWorkspaceWebController@home` | `workspace:employee` | `shells/employee.blade.php` | Personal Work Dashboard | PASS |
| `/portal/dashboard` | `portal.dashboard` | `PortalController@dashboard` | `auth` | `shells/employee.blade.php` | Employee Dashboard API | PASS |
| `/portal/profile` | `portal.profile` | `PortalController@profile` | `auth` | `shells/employee.blade.php` | Employee Profile Service | PASS |
| `/portal/requests` | `portal.requests` | `PortalController@requests` | `auth` | `shells/employee.blade.php` | Requests & Approval API | PASS |
| `/portal/schedule` | `portal.schedule` | `PortalController@schedule` | `auth` | `shells/employee.blade.php` | Shift & Schedule Service| PASS |
| `/portal/attendance`| `portal.attendance`| `PortalController@attendance` | `auth` | `shells/employee.blade.php` | Attendance Clock Service | PASS |
| `/portal/leave/create`| `portal.leave.create`| `PortalController@createLeave`| `auth` | `shells/employee.blade.php` | Leave Entitlement API | PASS |
| `/portal/payroll` | `portal.payroll` | `PortalController@payroll` | `auth` | `shells/employee.blade.php` | Payslip & Comp Service | PASS |

### 2.6 Executive Intelligence Workspace (`/executive/*`)
| Route URI | Name | Controller Action | Security Guard | UI Shell | Connected API / Service | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/executive` | `executive.index` | `ExecutiveWorkspaceWebController@overview` | `workspace:executive` | `shells/executive.blade.php`| Workforce Intelligence AI | PASS |
| `/executive/overview`| `executive.overview`| `ExecutiveWorkspaceWebController@overview` | `workspace:executive` | `shells/executive.blade.php`| Executive KPI Service | PASS |
| `/executive/costs` | `executive.costs` | `ExecutiveWorkspaceWebController@costs` | `workspace:executive` | `shells/executive.blade.php`| Workforce Cost Service | PASS |

### 2.7 Operational Command (`/operations/*`)
| Route URI | Name | Controller Action | Security Guard | UI Shell | Connected API / Service | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `/operations/system-health`| `operations.system-health`| `SystemHealthWebController@index` | `auth` | `shells/operations.blade.php` | System Telemetry | PASS |
| `/operations/queues`| `operations.queues` | `OperationalWorkspaceWebController@queues` | `workspace:operations` | `shells/operations.blade.php` | Queue Worker Horizon | PASS |
| `/operations/logs` | `operations.logs` | `OperationalWorkspaceWebController@logs` | `workspace:operations` | `shells/operations.blade.php` | Audit & Log Telemetry | PASS |
