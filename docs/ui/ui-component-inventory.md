# Enterprise UI Component Inventory

## 1. Application Shells (`resources/views/shells/`)

1. `platform.blade.php`: Platform Super Admin Control Center shell.
2. `tenant.blade.php`: Organization Administrator portal shell.
3. `hr.blade.php`: HR Operations Command Center shell.
4. `manager.blade.php`: Manager Workspace shell.
5. `employee.blade.php`: Employee Self-Service shell.
6. `executive.blade.php`: Executive Workforce Intelligence shell.
7. `operations.blade.php`: System Operations & Queue Diagnostics shell.

---

## 2. Reusable Shell Components (`resources/views/components/shells/`)

1. `header.blade.php`: Dynamic top navigation with contextual search, notifications, AI button, tenant badge, and user dropdown.
2. `workspace-switcher.blade.php`: Dropdown displaying user's authorized workspaces and handling POST transitions.
3. `breadcrumbs.blade.php`: Business context breadcrumbs.
4. `stat-card.blade.php`: Formatted KPI card with status indicators.
5. `empty-state.blade.php`: Empty collection visual placeholder.
6. `loading-state.blade.php`: Non-blocking spinner state.
7. `error-state.blade.php`: Standardized error alert.
