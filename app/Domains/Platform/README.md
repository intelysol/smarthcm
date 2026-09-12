# Platform Foundation

The Platform domain is the application-neutral foundation for FEP modules. It
provides tenant context, identity workflows, RBAC, profile/preferences,
navigation state, platform notifications, flags, settings and audit read APIs.

## Local development limitation

The supplied Windows PHP runtime lacks ZIP, POSIX and PCNTL support and its Git
runtime cannot fetch HTTPS remotes. Install the requested production packages
from a Linux or correctly provisioned developer environment:

```bash
composer require laravel/sanctum laravel/horizon laravel/reverb inertiajs/inertia-laravel
npm install react react-dom @inertiajs/react @tanstack/react-query @tanstack/react-table react-hook-form zod @hookform/resolvers
php artisan install:api
php artisan horizon:install
php artisan reverb:install
```

Horizon requires POSIX and PCNTL and must run in Linux production. API routes
remain compatible with Sanctum's `auth:sanctum` middleware after installation.

## Tenant contract

Authenticated requests provide the tenant through `X-Tenant` (tenant UUID or
slug) or an approved tenant subdomain. The resolved tenant must match the user
and be active. Never accept a tenant identifier from a request payload.
