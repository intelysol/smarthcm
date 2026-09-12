# Integration Hub technical design

The `Integration` bounded context is the single gateway for external
communication. Tenant-owned connections reference reusable connector manifests;
credentials are encrypted by the model cast and are never returned as plain
configuration by the API. Applications publish normalized integration events
and subscribe through webhook records rather than calling external services
directly.

Events are persisted before delivery, include a tenant and subject context, and
are delivered asynchronously with an HMAC signature. The existing sync-run
and dead-letter tables provide the durable extension point for queue workers,
retry policies and replay tooling. Connector implementations should implement
the service contract behind `IntegrationService`, allowing REST, SOAP, SFTP,
queue and vendor-specific adapters without changing domain APIs.
