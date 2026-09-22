# Operational Runbook: Webhook Delivery & Replay Triage

## 1. Overview
- **ID:** `rb-webhook-failure`
- **Severity:** SEV-3 (Minor) / SEV-2 (Significant if enterprise customer)
- **Component:** Webhook Dispatcher / HMAC Signer / Inbound & Outbound Webhook Pipelines
- **Trigger:** `webhook_failure_rate > 10.0%` over 15 minutes.

---

## 2. Immediate Diagnostic Steps
1. **Inspect Webhook Delivery Logs & Failed Dispatches:**
   ```bash
   grep -i "WebhookSecurityService" storage/logs/laravel.log | tail -n 20
   ```
2. **Inspect Delivery Attempts by Tenant:**
   - Check if failures are isolated to a specific tenant's endpoint or cluster-wide.
3. **Check TLS Handshake & SSL Certificate of Destination:**
   ```bash
   curl -Iv https://destination-customer-endpoint.com/webhook
   ```

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Customer Server Returning 5xx or Timing Out**
  - Verify automatic retry schedule: 1m, 5m, 15m, 1h, 6h, 24h.
  - If customer endpoint fails continuously for 24h, automatically disable webhook subscription to prevent worker queue congestion.
- **Scenario B: HMAC Signature Mismatch on Inbound Webhook**
  - Check timestamp drift: `WebhookSecurityService` enforces maximum 300s clock skew.
  - Verify webhook secret is correctly stored and decrypted.
- **Scenario C: Replay Attack Detected**
  - If `Webhook timestamp exceeds allowable drift` or nonce collision occurs:
    - Log security event to `App\Domains\Audit`.
    - Reject request with HTTP 401 Unauthorized.

---

## 4. Verification & Post-Resolution
- Re-dispatch failed webhook test payload from Tenant Integrations UI.
- Verify HTTP 200/202 status code received and logged.
