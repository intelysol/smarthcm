# Enterprise API Architecture and Standards

All public platform contracts use versioned plural resources under `/api/v1`,
tenant resolution from server context, permission checks, and predictable
response envelopes. `ApiResponse` provides the standard success/error shape
with correlation trace IDs; existing resources can adopt it incrementally.

The API governance registry catalogs method, path, schemas, security, and
status. Tenant client registrations support partner, internal, mobile, and
service-account consumers; only a SHA-512 hash is persisted for generated API
keys, and the plaintext key is returned once at creation.

The idempotency table is reserved for payment, payroll, import, and long-running
mutations. It must be applied by middleware at the gateway boundary, keyed by
tenant, method, path, and client-provided idempotency key. Sunset/version
headers, OpenAPI generation, cursor pagination, sparse fieldsets, and rate
limits are compatibility extensions over this contract.
