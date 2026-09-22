# Roles & Permissions Architecture

The Flow Enterprise Platform uses a granular Role-Based Access Control (RBAC) model combined with tenant-aware workspace authorization.

---

## Standard System Roles

| Role Key | Name | Primary Workspace | Scope |
| :--- | :--- | :--- | :--- |
| `platform_admin` | Platform Super Admin | `/platform` | System-wide (Cross-tenant) |
| `tenant_admin` | Tenant Administrator | `/admin/dashboard` | Tenant-wide |
| `hr_admin` | HR Administrator | `/hr/dashboard` | Tenant HCM Domain |
| `manager` | People Manager | `/manager/workbench` | Department / Direct Reports |
| `employee` | Employee Self-Service | `/portal` | Individual Record Only |
| `executive` | Executive Viewer | `/executive/overview` | Tenant Strategic View |
| `operations` | Reliability Engineer | `/operations/dashboard` | Infrastructure & Telemetry |

---

## Role-to-Workspace Routing

Upon authenticating through `/login`, the `LoginWebController` determines the appropriate destination:

```text
User Logs In
    │
    ▼
Is Platform Admin? ──── Yes ───► Redirect to /platform
    │
    No
    ▼
Has Tenant Admin Role? ── Yes ──► Redirect to /admin/dashboard
    │
    No
    ▼
Has HR Admin Role? ───── Yes ──► Redirect to /hr/dashboard
    │
    No
    ▼
Has Manager Role? ────── Yes ──► Redirect to /manager/workbench
    │
    No
    ▼
Default ────────────────────────► Redirect to /portal
```

---

## Server-Side Authorization

In addition to routing, all workspace route groups are protected by the `App\Http\Middleware\EnsureWorkspaceAccess` middleware (aliased as `workspace:{workspace}`). For example:
- A user with only the `employee` role accessing `/admin/dashboard` will receive an HTTP `403 Forbidden` response.
- Cross-tenant data tampering is blocked by tenant scoping middleware on all Eloquent models.