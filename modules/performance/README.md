# Performance

Tenant-isolated Performance Management for goals, performance cycles, reviews, feedback, ratings, outcomes, development, PIP, recognition, and history.

## Public API

The versioned API is mounted at `/api/v1/hcm/performance` and `/api/v1/hcm/me/performance`. See `app/Domains/Performance/Routes/api.php` for the current surface.

## Boundaries

This context never writes payroll, compensation, benefits, attendance, or recruitment data. Integration happens only through the documented provider contracts and Performance domain events.
