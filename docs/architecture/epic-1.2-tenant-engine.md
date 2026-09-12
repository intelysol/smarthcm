# Epic 1.2 — Enterprise Multi-Tenant Engine

## Technical design

The existing UUID-keyed `tenants` table is retained for backward compatibility. This increment extends it with public UUID, lifecycle state, tenant code, business identity, localization, branding reference, timestamps, and optimistic versioning. It adds tenant memberships (`tenant_user`) and the required tenant configuration, domain, invitation, audit, and usage tables.

`TenantContext` is request-scoped and immutable once resolved. Resolution prefers a server-side session tenant selection, falls back to the supported header/domain compatibility paths, validates tenant availability, and validates an active membership (or explicit platform administration) before establishing context. Tenant switches regenerate the session and audit-sensitive lifecycle changes are written to `tenant_audit_logs`.

The legacy `users.tenant_id` relationship remains supported while the migration backfills `tenant_user`. This prevents breaking existing authentication and domain models while allowing multi-tenant memberships. Tenant-specific settings, branding, and feature configuration have their own records and only use the trusted tenant context.

## API surface

- Platform tenant administration: `/api/v1/platform/tenants`
- Membership discovery and switching: `/api/v1/me/tenants`, `/api/v1/me/tenants/switch`
- Current-tenant settings, branding, features, and invitations: `/api/v1/tenant/*`

## Deliberate scope boundaries

The repository has no React/Inertia or shadcn runtime installed; consequently, no frontend bundle is added in this backend increment. The supplied `html-starter-kit` remains the visual reference for a future React/Tailwind implementation. Queue, search, cache, storage, analytics, and notification consumers already have varied legacy implementations and require their own incremental adoption work to use the new context contracts.

## Verification

Focused platform regression and tenancy-engine tests cover context clearing, legacy platform behavior, idempotent provisioning, authorized switching, lifecycle audit, and foreign-context rejection. The production MySQL migration must be run where MySQL is available; the local MySQL service was unavailable during verification.
