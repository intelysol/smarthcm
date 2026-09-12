# Enterprise Metadata Repository

The Metadata domain is the platform's metadata source of truth. It stores
business entities, fields, forms, relationships, records, and typed artifacts
for UI, validation, workflow, rules, API, analytics, AI, integration, and
security consumers.

Artifacts are versioned and lifecycle-controlled (`draft`, `approved`,
`published`, `deprecated`, `archived`). Published resolution is cached by
tenant and follows the inheritance order user, department, tenant, then
platform. `GET /api/v1/metadata/resolve/{type}/{key}` is the stable consumer
contract; cache invalidation is driven by `MetadataChanged`.

All mutations are audited, entity/relationship operations enforce tenant
boundaries, and metadata records are validated from field configuration. The
repository intentionally keeps customer-specific definitions in JSON metadata
instead of requiring application schema changes.

## Extension categories

Artifacts support forms, views, layouts, menus, components, and validations;
the same contract can represent workflow, rule, API, analytics, AI, and
integration definitions as additional artifact types without changing the
storage model.
