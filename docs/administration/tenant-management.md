# Tenant Management & Isolation Architecture

Multi-tenancy in the Flow Enterprise Platform is engineered for strict enterprise isolation, regulatory compliance, and scalability.

---

## Multi-Tenancy Architecture

The platform uses a shared database with discriminator column (`tenant_id`) and automated Eloquent Global Scopes.

```text
┌──────────────────────────────────────────────────────────┐
│                   Global Request Entry                   │
└────────────────────────────┬─────────────────────────────┘
                             │
                             ▼
┌──────────────────────────────────────────────────────────┐
│          TenantContext / Workspace Middleware            │
│  - Extracts tenant from authenticated user / domain      │
│  - Sets current tenant in container context              │
└────────────────────────────┬─────────────────────────────┘
                             │
                             ▼
┌──────────────────────────────────────────────────────────┐
│             Eloquent Multi-Tenancy Scoping               │
│  - Automatically filters: WHERE tenant_id = current_id   │
│  - Automatically sets tenant_id on model creation        │
└──────────────────────────────────────────────────────────┘
```

---

## Tenant Provisioning Process

When a new customer signs up:
1. A record is created in the `tenants` table with unique slug and domain bindings.
2. A default legal entity `Company` record is created.
3. Standard roles and default department structures are initialized.
4. An initial `tenant_admin` user is provisioned and delivered activation instructions.

---

## Cross-Tenant Data Leakage Prevention

- **Model Scoping**: All tenant models use the `TenantScoped` trait.
- **Foreign Key Enforcement**: All relationship queries verify foreign tenant ownership.
- **Route Model Binding**: Route model binders reject entity lookups whose `tenant_id` does not match the active session.