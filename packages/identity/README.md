# Identity

The tenant-aware identity provider for every Flow application. It owns authentication,
password lifecycle, MFA enrollment, device/session tracking, security attempts, and
identity API contracts. RBAC remains a separate platform capability.

## API

Endpoints are versioned below `/api/v1/identity`: login/logout, password reset,
email verification, profile, password change, MFA, and session revocation. Authenticated
endpoints require a trusted `X-Tenant` header (or tenant subdomain) and the `tenant`
middleware validates membership.

## Security

Login and password-reset endpoints use rate limiters. Password changes reject the five
most recent passwords; user sessions, fingerprints, IPs, and login attempts are audited.
TOTP enrollment returns recovery codes once; applications must display them only once.
