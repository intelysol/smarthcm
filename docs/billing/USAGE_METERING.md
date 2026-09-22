# Usage Metering, Idempotency & Aggregation

## 1. Metering Architecture

Usage metering provides centralized, tenant-scoped tracking of consumption metrics across the platform:
- `active_employees` (Active employee roster records)
- `user_seats` (Active tenant user credentials)
- `api_calls` (External and internal API gateway requests)
- `ai_tokens` (AI Concierge and AI Operations model tokens)
- `storage_mb` (Employee documents and attachment bytes)

---

## 2. Strict Idempotency

Usage events sent via webhooks, queue workers, or batch ingestion must not be double counted.
- Each event is recorded with an `idempotency_key` unique constraint in `billing_usage_events`.
- If an event with an existing key is received, it is deduplicated and safely acknowledged with `'idempotency_status' => 'ignored_duplicate'`.

---

## 3. Pre-aggregated Snapshots

To prevent running slow, resource-intensive `SUM()` queries across millions of raw event rows on dashboard page loads:
- The scheduled worker aggregates raw events periodically into `billing_usage_snapshots`.
- Dashboard and entitlement checks query recent snapshots plus small incremental deltas, executing in under 5ms.

---

## 4. Threshold Alerting

The `UsageMeteringService::evaluateThreshold()` checks consumption against plan quotas:
- **80%**: `NOTICE_80_PERCENT`
- **90%**: `WARNING_90_PERCENT`
- **100%**: `CRITICAL_100_PERCENT` (Triggers upgrade banners and restricts quota expansion)
