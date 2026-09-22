# User Management Architecture & Operations

This document describes how user accounts, authentication sessions, and identity lifecycles are handled in the Flow Enterprise Platform.

---

## User Data Model

User accounts are represented by the `App\Models\User` model. Key columns include:

| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Unique internal user identifier |
| `tenant_id` | bigint (FK) | Scopes the user to their tenant organization (NULL for global platform super admins) |
| `name` | string | Full display name |
| `email` | string (unique) | Login identifier |
| `password` | string (hash) | Bcrypt/Argon2 password hash |
| `status` | string | Account status: `active`, `inactive`, `suspended` |
| `is_platform_admin`| boolean | Flag granting cross-tenant control center access |
| `last_login_at` | timestamp | Recorded upon successful web or API authentication |

---

## User Management Operations

### In Tenant Admin Portal (`/admin/users`)
Tenant administrators can manage all users within their tenant organization:
1. **Creation**: Provision new users with email, name, role, and temporary password.
2. **Deactivation**: Toggle status to `inactive`. Inactive accounts are immediately rejected by `LoginWebController` upon next login attempt.
3. **Password Reset**: Admin can reset the user's password directly from the user table modal.

### In Platform Super Admin Portal (`/platform/users`)
Platform Super Admins can search across all tenants, view login telemetry, and deactivate compromised accounts across the entire platform.

---

## Account Security Best Practices
- Passwords must meet enterprise complexity standards (minimum 8 characters with numbers and special symbols).
- Accounts locked due to repeated failed attempts can be reset by a tenant or platform administrator.
- Session tokens are invalidated immediately upon user status revocation.