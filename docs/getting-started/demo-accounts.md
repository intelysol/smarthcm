# Demo Accounts & Credentials Guide

The Flow Enterprise Platform includes a comprehensive development and demonstration environment pre-populated with realistic organizational structures, hierarchical reporting lines, and 5 distinct personas.

---

## Demo Personas Overview

```text
Platform Super Admin (Global SaaS Control Plane)
       │
       ▼
Tenant Admin (Smart HCM Demo Organization)
       │
       ▼
HR Administrator (Director of Human Resources)
       │
       ▼
People Manager (Engineering Manager)
       │
       ▼
Employee (Senior Software Engineer)
```

---

## Persona Credentials Reference

| Role | Name | Email | Password | Initial Redirect Destination |
| :--- | :--- | :--- | :--- | :--- |
| **Platform Super Admin** | Platform Super Administrator | `superadmin@example.test` | `Demo1234!@#$` | `/platform` |
| **Tenant Admin** | Acme Enterprise Admin | `admin@example.test` | `Demo1234!@#$` | `/admin/dashboard` |
| **HR Administrator** | Eleanor Vance | `hr@example.test` | `Demo1234!@#$` | `/hr/dashboard` |
| **People Manager** | Marcus Sterling | `manager@example.test` | `Demo1234!@#$` | `/manager/workbench` |
| **Employee** | Sophia Chen | `employee@example.test` | `Demo1234!@#$` | `/portal` |

*Note: In local and development environments, quick-fill buttons appear on the login page (`/login`) to enable one-click authentication as any persona.*

---

## Configuring Demo Passwords

The default password for all demo accounts is `Demo1234!@#$`. You can override this default across all seeders by specifying the `DEMO_USER_PASSWORD` environment variable in your `.env` file:

```ini
DEMO_USER_PASSWORD="CustomSecurePassword2026!"
```

When you re-run `php artisan app:setup-demo`, accounts will be updated to use this password.

---

## Production Safety Enforcement

> [!CAUTION]
> Demo accounts MUST NEVER be inadvertently seeded into production environments.

The `DemoEnvironmentSeeder` and Artisan commands enforce strict production safeguards:
- If `APP_ENV=production` and `SEED_DEMO_USERS` is NOT set to `true`, the seeder halts immediately and throws a `RuntimeException`.
- The `app:reset-demo` command is **permanently prohibited** from running when `APP_ENV=production`.

To intentionally enable demo accounts in an isolated production demo instance:
```ini
APP_ENV=production
SEED_DEMO_USERS=true
```

---

## CLI Management Commands

### 1. Setup Demo Environment
```bash
php artisan app:setup-demo
```
Idempotently creates or updates the demo tenant, company, departments, positions, roles, and the 5 demo users.

### 2. Reset Demo Environment
```bash
php artisan app:reset-demo --force
```
Safely deletes existing demo user accounts and tenant structures, then re-seeds them with pristine default values.