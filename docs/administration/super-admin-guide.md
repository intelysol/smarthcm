# Platform Super Admin Guide

The **Platform Super Admin** is the apex administrator of the Flow Enterprise Platform. This role operates across the global control plane and is responsible for multi-tenant management, system-wide infrastructure, AI model governance, security posture, and global audit logging.

---

## Key Capabilities

1. **Tenant Provisioning & Lifecycle**:
   - Create new customer organizations and provision tenant workspaces.
   - Configure tenant domain bindings, database isolation, and storage quotas.
   - Suspend, archive, or offboard delinquent or cancelled tenants.

2. **Global System Health & Queues**:
   - Access real-time Horizon queue telemetry and job throughput metrics (`/operations/queues`).
   - Monitor Redis cache memory consumption and database connection pools (`/operations/system-health`).
   - View system-wide error logs and audit trails (`/operations/logs`).

3. **Security & AI Governance**:
   - Review platform-wide authentication events, failed logins, and privilege escalations (`/platform/security`).
   - Manage enterprise LLM integrations, model token budgets, and data redacting safety filters (`/platform/ai-governance`).

4. **Platform User Administration (`/platform/users`)**:
   - View all users across the platform with tenant indicators and role tags.
   - Toggle account status (Active / Inactive) across any tenant to resolve security emergencies.
   - Designate or revoke global Platform Admin privileges.

---

## Navigation & Workspaces

The Super Admin has unrestricted access to all platform views. The primary navigation includes:
- **Control Center**: `/platform`
- **Tenants & Provisioning**: `/platform/tenants`
- **Products & Billing**: `/platform/billing`
- **Integrations Hub**: `/platform/integrations`
- **System Health**: `/operations/system-health`
- **Platform Users**: `/platform/users`
- **Help Center**: `/help`