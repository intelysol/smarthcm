# Enterprise Performance Architecture & Measurement Model

## 1. Executive Summary
The Enterprise Application Platform enforces an end-to-end, multi-layered performance engineering model. Performance is treated as a continuous system constraint rather than an afterthought. Every layer in the request lifecycle is metered, budgeted, and isolated to prevent systemic degradation.

---

## 2. Seven-Layer Measurement Architecture

```text
[Layer 1: Frontend UI]
  → Bundle splitting, server-side pagination, virtualized lists, instant feedback.
       ↓
[Layer 2: Ingress & API Gateway]
  → Fast TLS termination, CaptureCorrelationId middleware, rate-limiting tokens.
       ↓
[Layer 3: Authentication & Tenancy]
  → TenantContext resolution, version-tagged permission cache (0 DB queries).
       ↓
[Layer 4: Application & Domain Layer]
  → Lean domain services, DTO transformations, bounded memory allocations.
       ↓
[Layer 5: Database & Query Layer]
  → Composite tenant indexes, N+1 eager loading, cursor-based streaming.
       ↓
[Layer 6: In-Memory Cache & Redis]
  → Multi-level caching (L1 in-memory array, L2 Redis Sentinel), TTL governance.
       ↓
[Layer 7: Background Queues & Workers]
  → Queue tiering (critical/high/default/bulk), Horizon worker scaling.
```

---

## 3. Core Performance Optimization Rules

### 1. Zero Unindexed Tenant Queries
Every high-frequency query on a tenant-aware table must include `tenant_id` as the leading column in a composite index (e.g. `(tenant_id, status)`, `(tenant_id, created_at)`). Full-table scans are blocked in CI via strict query plan assertion.

### 2. Mandatory N+1 Query Elimination
Relations rendered in loops must be eager loaded explicitly with specific column projections:
```php
// Good: Single query with projected columns
$employees = Employee::query()
    ->with(['department:id,name', 'position:id,title'])
    ->where('tenant_id', $tenantId)
    ->paginate(25);
```

### 3. Cursor-Based Chunking for Large Datasets
Loading large collections into memory is prohibited. Heavy workflows must use `lazyById()` or chunking:
```php
// Good: Bounded memory footprint
PayrollRun::query()
    ->where('tenant_id', $tenantId)
    ->lazyById(250)
    ->each(function ($item) {
        $this->processItem($item);
    });
```

### 4. Queue Tiering & Starvation Prevention
Long-running batch exports and payroll calculations must never share workers with interactive notifications. Queues are prioritized as:
- `critical`: MFA, password resets, real-time alerts.
- `high`: Shift punch reconciliations, workflow transitions.
- `default`: General notifications, avatar generation.
- `bulk`: Mass CSV imports, heavy analytical report exports.
