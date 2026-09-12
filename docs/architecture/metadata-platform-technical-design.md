# Metadata Platform Technical Design

## Ownership and boundaries

The `Metadata` bounded context owns runtime configuration only. It never
creates application tables or Eloquent relations dynamically. Applications
remain owners of their domain data and may consume published metadata through
the Metadata API. Dynamic records use the existing JSON `metadata_records`
store and remain tenant scoped.

## Aggregate design

- `MetadataEntity` describes a configurable business entity and carries the
  entity type, category, appearance, lifecycle, audit and soft-delete options.
- `MetadataField` belongs to one entity. Its configuration contains default,
  placeholder, tooltip, read-only, hidden, conditional and localized values;
  validation is structured and evaluated by the service before record writes.
- `MetadataRelationship` captures cardinality and selectors without attempting
  to mutate database schemas.
- `MetadataArtifact` provides versioned definitions for `form`, `view`,
  `layout`, `menu`, `component`, and `validation` artifacts. This is the
  extensibility boundary for builders and future React drag-and-drop editors.
- `MetadataRecord` owns custom JSON data. It is intentionally separate from
  configuration and is validated against published field definitions.

## Security and tenancy

Every request is executed under Platform's `TenantContext`; tenant identifiers
are never read from metadata payloads. Policies require the platform metadata
permissions. System metadata uses a null tenant and is readable only when
explicitly included by the consumer. Entity access, field edits, artifact
publishing and record changes produce metadata audit entries and platform
activity logs.

## Runtime API and cache

`/api/v1/metadata` provides tenant-scoped entity, field, relationship,
artifact, runtime-definition and dynamic-record endpoints. Entity definitions
are cached by tenant/entity/version. Any metadata write increments the entity
version, removes the cache key, and emits a `MetadataChanged` event. The event
is the integration point for analytics, search indexing, automation, AI context
providers, Reverb notification, and Redis/Horizon warm-up in production.

## Database compatibility

The pre-existing migration from 2026-07-13 creates the base metadata tables.
The Phase 2 migration enriches them with missing descriptive fields, audit
attribution, optimistic-lock versions, deletion state, and tenant-aware
indexes. Existing data is preserved. No application-specific columns are
introduced.

## Frontend delivery

The repository still has no installable React/Inertia runtime because its local
Windows PHP/Git environment cannot install the required packages. The stable
runtime-definition API is complete for the requested React pages; builder page
source must be generated once that toolchain is bootstrapped, rather than
committing unbuildable UI code.
