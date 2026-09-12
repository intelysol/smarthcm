# Platform Foundation Operations and API Guide

## Installation

Run migrations and seed the permission catalog after deploying the platform:

```bash
php artisan migrate --force
php artisan db:seed --class=PlatformFoundationSeeder --force
```

Every authenticated Platform API request requires `X-Tenant: <tenant UUID or
slug>`. The middleware rejects inactive and cross-tenant access.

## API conventions

All foundation APIs are under `/api/v1/platform`. Lists use `per_page` (1-100),
`search`, `sort`, and `direction`. Responses expose API resources rather than
models. Invalid payloads use Laravel's standard 422 validation document.

| Purpose | Method and route | Permission |
| --- | --- | --- |
| Sign in | `POST auth/login` | Public, tenant and account checked |
| Current profile | `GET/PUT me` | Authenticated user |
| Change password | `PUT me/password` | Authenticated user |
| Dashboard | `GET dashboard` | Authenticated tenant user |
| List roles | `GET roles` | `platform.roles.view` |
| Create/edit roles | `POST roles`, `PUT roles/{id}/permissions` | `platform.roles.manage` |
| Assign role | `POST roles/{id}/users` | `platform.roles.manage` |
| Notifications | `GET notifications`, `PUT notifications/{id}/read` | Authenticated user |
| Audit | `GET audit/activities`, `GET audit/login-history` | `platform.audit.view` |
| Flags and settings | `GET feature-flags`, `GET/PUT settings` | `platform.settings.manage` |

## Production setup

Use Redis for cache, queue, and broadcasts. Install Sanctum, Horizon, Reverb,
Inertia and React in a Linux environment with ZIP, POSIX, and PCNTL support;
the exact commands are in the [Platform module README](../app/Domains/Platform/README.md).
Run Horizon separately with supervisor/systemd, and expose Reverb only behind a
TLS-terminating reverse proxy. Configure trusted hosts so untrusted Host headers
cannot select a tenant.
