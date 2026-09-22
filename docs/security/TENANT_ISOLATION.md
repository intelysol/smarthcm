# Multi-Tenant Security & Strict Cross-Tenant Isolation

## 1. Isolation Architecture

The Enterprise Application Platform operates a shared-database, shared-process multi-tenant architecture with **logical and cryptographic isolation** across every layer.

Under no circumstances may data belonging to Tenant A be viewed, mutated, deleted, exported, searched, or processed by Tenant B.

---

## 2. Seven Layers of Tenant Isolation

### 1. HTTP Ingress & Middleware Isolation
- All authenticated web and API requests pass through `ResolveTenant` middleware.
- The tenant context is resolved from session (`tenant_uuid`), verified headers (`X-Tenant`), or validated subdomains.
- `abort_unless($user->canAccessTenant($tenant), 403, 'Tenant access denied.')` ensures users cannot access tenants they do not belong to.
- `RequestTenantContext` binds the tenant immutably to the request lifecycle. Any attempt to modify or overwrite the context during execution throws a `LogicException`.

### 2. Database & Eloquent Query Isolation
- All tenant-owned models implement the `BelongsToTenant` concern trait:
  ```php
  static::addGlobalScope('tenant', function (Builder $builder): void {
      $tenantId = app(TenantContext::class)->id();
      if ($tenantId !== null) {
          $builder->where($builder->qualifyColumn('tenant_id'), $tenantId);
      }
  });
  ```
- On model creation, `tenant_id` is forcefully injected from the authenticated `TenantContext`. Attempts to pass a different `tenant_id` via HTTP request body are overwritten or rejected.

### 3. File & Document Storage Isolation
- Private documents are partitioned into tenant-specific filesystem/S3 prefixes:
  `storage/app/tenants/{tenant_uuid}/documents/{document_uuid}.enc`
- Document downloads require prior resolution of `EmployeeDocumentSecurityService`, checking tenant ownership before generating ephemeral pre-signed URLs.

### 4. Cache & Session Isolation
- Cache keys for tenant-sensitive data MUST always be prefixed with the tenant UUID:
  `tenant:{tenant_id}:{cache_key}`
- Session data stores `tenant_uuid` locked to the authenticated user record.

### 5. Asynchronous Queues & Background Jobs
- Every queued job (e.g. bulk document processing, scheduled reporting, payroll calculations) carries an explicit `$tenantId` property in its serialized payload.
- Upon job invocation, the worker restores `TenantContext` for that specific tenant before running domain logic, and explicitly clears it upon completion.
- Cross-tenant payload execution is prevented by verifying that the target model belongs to the job's assigned tenant.

### 6. Search & Full-Text Indexing Isolation
- Search indexes (Meilisearch / Elasticsearch / database search) include a mandatory filter: `tenant_id = :current_tenant_id`.
- Unfiltered search across tenants is strictly prohibited at the query construction layer.

### 7. AI Operations & Prompt Context Isolation
- When employee concierge or AI advisory agents query the knowledge base, vector embeddings, or relational records, queries are hard-scoped to `tenant_id`.
- Cross-tenant RAG (Retrieval-Augmented Generation) context bleeding is mathematically impossible due to mandatory metadata filtering on vector queries.

---

## 3. Verification & Automated Certification

Multi-tenant isolation is continuously tested via `Tests\Security\TenantIsolationTest`:
- **Header Spoofing:** Supplying `X-Tenant: foreign-tenant-id` while logged into Tenant A returns `403 Forbidden`.
- **Tenant Switching:** Attempting to switch to an unauthorized tenant via `/api/v1/me/tenants/switch` returns `403 Forbidden`.
- **Cross-Tenant API Gateways:** API keys for Tenant A cannot read or write resources of Tenant B.
- **Cache Separation:** Key collisions across tenants are verified to return distinct isolated values.
