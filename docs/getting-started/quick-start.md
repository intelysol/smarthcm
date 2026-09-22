# 5-Minute Quick Start Guide

Welcome to the **Flow Enterprise Platform (Smart HCM)**! Follow this quick-start guide to be up and running with a fully functional enterprise HRMS in under five minutes.

---

## Quick Setup Summary

```bash
# 1. Clone & enter repository
git clone https://github.com/intelysol/smarthcm.git
cd smarthcm

# 2. Install PHP and JS dependencies
composer install
npm install && npm run build

# 3. Configure environment and database
cp .env.example .env
php artisan key:generate

# 4. Migrate database and seed demo personas
php artisan migrate
php artisan app:setup-demo

# 5. Launch the local web server
php artisan serve
```

---

## Accessing the Application

Navigate to [http://localhost:8000](http://localhost:8000). On the login page, you can choose any persona using the quick-select buttons or entering credentials manually:

| Persona | Email | Default Password | Target Workspace |
| :--- | :--- | :--- | :--- |
| **Platform Super Admin** | `superadmin@example.test` | `Demo1234!@#$` | `/platform` (Global SaaS Control Plane) |
| **Tenant Admin** | `admin@example.test` | `Demo1234!@#$` | `/admin/dashboard` (Organization Management) |
| **HR Administrator** | `hr@example.test` | `Demo1234!@#$` | `/hr/dashboard` (HCM Operations Center) |
| **People Manager** | `manager@example.test` | `Demo1234!@#$` | `/manager/workbench` (Team Management) |
| **Employee** | `employee@example.test` | `Demo1234!@#$` | `/portal` (Self-Service Portal) |

---

## Exploring the Workspaces

1. **Platform Control Center (`/platform`)**: Explore multi-tenant provisioning, cross-tenant telemetry, security posture, and platform administrator user accounts.
2. **Tenant Administration (`/admin/dashboard`)**: Manage company legal entities, departments (`/admin/departments`), job positions (`/admin/positions`), and active tenant users (`/admin/users`).
3. **HR Command Center (`/hr/dashboard`)**: Review employee lifecycle statistics, attendance rosters, recruitment funnels, and payroll processing.
4. **Manager Workbench (`/manager/workbench`)**: Review pending leave requests, team attendance, and schedule rosters.
5. **Employee Portal (`/portal`)**: Experience self-service clock-in, leave application, payslip retrieval, and privacy self-service.
6. **In-App Help Center (`/help`)**: Browse complete documentation, role guides, and troubleshooting runbooks directly in the browser.