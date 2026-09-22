# Operational Runbook: Third-Party Integration Partner Failure

## 1. Overview
- **ID:** `rb-integration-failure`
- **Severity:** SEV-2 (Significant)
- **Component:** Integration Hub / ERP Connectors (SAP, NetSuite, Workday, QuickBooks)
- **Trigger:** Outbound sync error rate > 5% or API partner HTTP 503/429 spikes.

---

## 2. Immediate Diagnostic Steps
1. **Inspect Integration Sync Logs:**
   ```bash
   grep -i "Integration" storage/logs/laravel.log | grep -E "ERROR|CRITICAL" | tail -n 20
   ```
2. **Check Circuit Breaker Status:**
   - Review `/health/dependencies` for external integration gateway statuses.
3. **Verify Partner API Credentials:**
   - Confirm OAuth tokens have not expired or been revoked by the third party.

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Third-Party Rate Limiting (HTTP 429)**
  - Enable exponential backoff in integration queue worker configuration.
  - Temporarily throttle integration sync batches to partner API threshold.
- **Scenario B: Partner Outage (HTTP 503)**
  - Engage Circuit Breaker: mark partner connector as `temporarily_unavailable`.
  - Queue outgoing sync operations with delayed retry timestamps (15m, 1h, 4h).
  - Notify tenant administrators via system notification of degraded sync status.
- **Scenario C: Authentication Revocation (HTTP 401)**
  - Flag tenant integration connection as `requires_reauthorization`.
  - Alert tenant admin to re-authenticate OAuth in Tenant Settings.

---

## 4. Verification & Post-Resolution
- Trigger test sync batch for single test tenant.
- Verify sync state transitions from `degraded` to `synced`.
