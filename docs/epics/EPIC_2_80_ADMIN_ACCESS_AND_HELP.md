# EPIC 2.80 — Admin & Super Admin Users, Demo Environment & Application Help Documentation

## Executive Summary
Epic 2.80 delivers a fully functional, enterprise-grade access control, demonstration, and in-application documentation layer for the **Flow Enterprise Platform (Smart HCM)**. It guarantees that new developers, operational personnel, evaluators, and system auditors can immediately run, authenticate, navigate, and understand the platform without tribal knowledge.

---

## Deliverables & Architectural Components

### 1. Pre-Configured Demo Personas (`database/seeders/DemoEnvironmentSeeder.php`)
Seeds 5 distinct personas with realistic organization data:
- **Platform Super Admin**: `superadmin@example.test` &rarr; Control Center (`/platform`)
- **Tenant Admin**: `admin@example.test` &rarr; Organization Administration (`/admin/dashboard`)
- **HR Administrator**: `hr@example.test` &rarr; HCM Operations Center (`/hr/dashboard`)
- **People Manager**: `manager@example.test` &rarr; Team Workbench (`/manager/workbench`)
- **Employee**: `employee@example.test` &rarr; Self-Service Portal (`/portal`)

### 2. Role-Based Dynamic Redirection (`LoginWebController.php`)
- Intelligently redirects users upon login to their authorized dashboard.
- Enforces user status (`active`, `inactive`, `suspended`) to reject deactivated accounts.
- Records `last_login_at` timestamps for auditability.

### 3. Production Safety Guard
- Automated check prevents accidental seeding in production environments unless `SEED_DEMO_USERS=true` is set.
- `app:reset-demo` command is strictly prohibited on production environments.

### 4. Artisan CLI Commands
- `php artisan app:setup-demo`: Idempotent seeding and credentials table output.
- `php artisan app:reset-demo`: Clean wipe and re-seed of demo accounts with confirmation flag.

### 5. In-App Help Center Engine (`/help`)
- Categorized knowledge base (`HelpCenterService.php`, `HelpWebController.php`).
- Searchable articles, estimated reading times, related guides sidebar, and clean responsive UI.
- Integrated into all workspace navigation headers via `NavigationRegistry.php`.

### 6. User Management Portals
- **Tenant Admin (`/admin/users`)**: Create users, toggle active/inactive status, reset passwords.
- **Platform Super Admin (`/platform/users`)**: Global multi-tenant user oversight and security lockouts.

### 7. Documentation Suite (`docs/`)
- Complete setup, administrative, HCM, and troubleshooting guides across `docs/getting-started/`, `docs/administration/`, `docs/hcm/`, and `docs/help/`.
- Updated comprehensive `README.md`.