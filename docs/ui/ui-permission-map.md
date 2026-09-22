# Enterprise UI Permission & Authorization Matrix

## 1. Access Control Invariants

UI visibility is driven by backend authorization. The middleware `App\Http\Middleware\EnsureWorkspaceAccess` guarantees that non-permitted roles are stopped before any HTML or data is rendered.

| Workspace | Route Namespace | Required Roles / Permissions | Non-Permitted Persona Action |
|:---|:---|:---|:---|
| **Platform Admin** | `/platform/*` | `is_platform_admin = true` OR `platform_admin` role | 403 Access Restricted &bull; Redirect to authorized workspace |
| **Tenant Admin** | `/admin/*` | `tenant_admin`, `admin`, or `tenant.settings.manage` | 403 Access Restricted &bull; Redirect to authorized workspace |
| **HR Operations** | `/hr/*` | `hr_admin`, `hr_manager`, `payroll_admin`, or `hr.*` | 403 Access Restricted &bull; Redirect to authorized workspace |
| **Manager** | `/manager/*` | Active Direct Reports (`reports_to_id`) OR `manager` role | 403 Access Restricted &bull; Redirect to authorized workspace |
| **Employee** | `/employee/*`, `/portal/*` | All authenticated accounts (`active` status) | Rendered without admin clutter |
| **Executive** | `/executive/*` | `c_suite`, `executive`, `director`, or `analytics.*` | 403 Access Restricted &bull; Redirect to authorized workspace |
| **Operations** | `/operations/*` | `operations`, `devops`, `admin`, or `system.health.view` | 403 Access Restricted &bull; Redirect to authorized workspace |

---

## 2. Multi-Role Personas & Switching

When a user possesses multiple roles (e.g. an HR Director who is also an employee with direct reports):
1. All permitted workspaces are rendered in the **Workspace Switcher** dropdown.
2. The user initiates `POST /workspace/switch` with `{ workspace: 'hr' | 'manager' | 'employee' }`.
3. The session records `active_workspace`.
4. The application transitions immediately to the target dashboard with role-appropriate layout, navigation, and security boundaries.
