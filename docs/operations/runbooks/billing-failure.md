# Operational Runbook: Commercial Billing & Payment Processing Failure

## 1. Overview
- **ID:** `rb-billing-failure`
- **Severity:** SEV-1 (Major)
- **Component:** Commercial Billing / Stripe / Webhook Handlers / Subscription Matrix
- **Trigger:** Payment webhook signature verification failures, invoice generation errors, or Stripe API 5xx spikes.

---

## 2. Immediate Diagnostic Steps
1. **Inspect Billing Webhook Logs:**
   ```bash
   grep -i "Stripe" storage/logs/laravel.log | tail -n 20
   ```
2. **Verify Stripe Webhook Secret:**
   - Confirm `STRIPE_WEBHOOK_SECRET` matches the active endpoint configured in Stripe Developer Dashboard.
3. **Inspect Failed Subscription Invoices:**
   - Query pending or failed invoice sync records in `tenant_subscriptions`.

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Stripe Webhook Secret Mismatch**
  - If incoming webhooks return HTTP 400 with invalid signature:
    1. Retrieve new webhook secret from Stripe dashboard.
    2. Update `STRIPE_WEBHOOK_SECRET` in production `.env`.
    3. Run `php artisan config:cache`.
    4. Replay missed webhooks from Stripe Dashboard.
- **Scenario B: Tenant Plan Entitlement Mismatch**
  - If a tenant paid but feature flags are not unlocked:
    - Run manual synchronization artisan command:
      ```bash
      php artisan billing:sync-tenant <tenant_uuid>
      ```
- **Scenario C: Mass Subscription Renewal Failure**
  - Verify Stripe API availability.
  - Do NOT cancel tenant subscriptions prematurely during payment provider downtime; grace period of 7 days is automatically maintained.

---

## 4. Verification & Post-Resolution
- Confirm `/portal/billing` renders accurate subscription status and invoice history.
- Verify webhook events log HTTP 200 OK.
