# Platform Foundation Technical Design

## Scope and ownership

The `Platform` bounded context owns reusable identity orchestration, tenant
resolution, roles, profile and preference data, navigation state, platform
notifications, feature flags, settings access, and audit viewing.  It does not
own organizational records: `Organization` remains the owner of companies,
branches, business units, departments, work locations, and cost centers.

## Existing capability and decisions

- Existing `tenants`, `settings`, `activity_logs`, organization records, direct
  user permissions, and employee portal notifications are retained.  Their
  current integer user keys and historical migrations are documented legacy
  exceptions to the UUID aggregate standard.
- The new Platform layer adds role-based permission aggregation without
  removing direct user permissions.  Effective access is the union of both.
- Tenant identity is resolved only from a trusted request header or subdomain;
  it is never accepted from a request body.  It must match the authenticated
  user's tenant and must be active.
- API endpoints are namespaced under `/api/v1/platform`, use server-side tenant
  scoping, request validation, policies, pagination, filtering and sorting.

## Dependencies and extension points

- `TenantContext` is the shared tenant contract. `ResolveTenant` binds it for
  API requests and is registered as route middleware.
- `ActivityLogService` remains the audit sink. Domain events are emitted for
  tenant onboarding, role assignment, profile updates, notifications and
  setting changes; listeners write the audit record and are suitable future
  integration, analytics, automation and AI consumers.
- Expensive notifications are sent through Laravel queues.  A Linux production
  deployment installs Sanctum, Horizon and Reverb; the local Windows runtime
  currently cannot install them because PHP ZIP, POSIX and PCNTL support are
  unavailable. See the Platform README for the deployment command.

## Data design

New UUID aggregates are `roles`, `user_profiles`, `user_preferences`,
`notification_preferences`, `platform_notifications`, `navigation_favorites`,
`recent_pages`, `feature_flags`, and `login_histories`.  All tenant-owned
tables have a tenant foreign key, composite tenant lookup indexes, timestamps,
and soft deletes where the record is user-managed.  Role permissions reuse the
existing permission catalog.  The user table gains non-sensitive account state
and localization columns; personal data lives in `user_profiles`.

## API surface

| Area | Endpoint family |
| --- | --- |
| Identity | `auth/login`, `auth/logout`, `auth/forgot-password`, `auth/reset-password`, `me`, `me/password` |
| Tenants | `tenants`, `tenants/{tenant}/activate`, `tenants/{tenant}/deactivate` |
| Users/RBAC | `users`, `roles`, `roles/{role}/permissions` |
| Experience | `navigation/favorites`, `navigation/recent-pages`, `notifications`, `settings`, `feature-flags`, `dashboard` |
| Audit | `audit/activities`, `audit/login-history` |

## Security and performance

Policies and permission middleware protect every endpoint. User and tenant
lookup queries are tenant scoped, passwords use Laravel hashing, and login
attempts are rate limited. API resources prevent model leakage. Composite
indexes cover the high-volume notification, audit, favorite, recent-page and
login-history queries. Dashboard metrics use aggregate queries and may be
cached by a future cache adapter; no cross-domain writes are introduced.

## UI delivery note

The repository currently has no React/Inertia runtime. Typed React page source
and the enterprise shell are intentionally staged behind the package bootstrap
documented in the Platform README; shipping unbuildable frontend code would
violate the platform release gate. The backend API contract is the stable
integration boundary for those pages.
