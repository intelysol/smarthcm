# Identity API

Base path: `/api/v1/identity`. Login accepts `tenant`, `identifier` (email or username),
`password`, and optional `remember`. Authenticated endpoints use the application session
and require tenant resolution through `X-Tenant` or a tenant subdomain.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/login` | Authenticate and record device/session |
| POST | `/logout` | Invalidate the current session |
| POST | `/forgot-password`, `/reset-password` | Password recovery |
| POST | `/verify-email` | Mark authenticated email verified |
| POST | `/change-password` | Enforce history and update credential |
| POST | `/enable-mfa`, `/disable-mfa` | Manage TOTP MFA |
| GET/DELETE | `/sessions`, `/sessions/{id}` | List/revoke active sessions |
| GET/PATCH | `/profile` | Read/update personal identity data |
