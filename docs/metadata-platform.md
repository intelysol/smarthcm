# Metadata Platform

The Metadata domain provides the low-code configuration layer used by all FEP
applications. It is tenant-scoped and does not mutate application schemas.

## Supported builders

Entities and dynamic fields are first-class APIs. Forms, views, layouts, menus,
reusable components and validation definitions are versioned `metadata_artifacts`.
Use artifact types `form`, `view`, `layout`, `menu`, `component`, and
`validation`; their JSON definitions are intentionally frontend-agnostic.

## API

All routes are under `/api/v1/metadata` and require authentication plus the
`X-Tenant` header. `metadata.entities.view` reads definitions;
`metadata.entities.manage` creates entities and fields; and
`metadata.records.manage` creates dynamic records.

| Route | Purpose |
| --- | --- |
| `GET/POST entities` | Filterable entity catalog and entity designer |
| `GET entities/{id}` | Entity with field/form definitions |
| `GET entities/{id}/definition` | Cached runtime definition for React clients |
| `POST entities/{id}/fields` | Dynamic field editor |
| `POST entities/{id}/records` | Validated custom record write |
| `GET/POST artifacts` | Versioned forms, views, layouts, menus, components and validation definitions |
| `GET/POST relationships` | Tenant-scoped relationship graph and designer |

Metadata writes increment entity versions, invalidate the metadata cache, write
metadata and platform audit entries, and emit `MetadataChanged` for future
analytics, automation, AI, search and notification listeners.

## Production cache and UI

Use Redis for the cache store and run a warm-up command/worker after production
deployment. The React/Inertia builder screens are intentionally deferred until
the dependency bootstrap documented in the Platform README can run; this local
environment cannot install that toolchain.
