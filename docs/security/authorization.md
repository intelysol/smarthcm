# Authorization

Authorization is evaluated by `AuthorizationService`, separately from authentication and tenant resolution. The decision order is explicit deny, explicit allow, direct permission, active tenant role permission, legacy role permission, then default deny. Authorization cache keys include tenant, user, version, and permission hash; role assignment invalidates the affected user's version.

All calls must begin with trusted `TenantContext`; client input cannot select a tenant for authorization. The new `user_roles` table supports effective dates and scopes while retaining `role_user` for legacy compatibility.
