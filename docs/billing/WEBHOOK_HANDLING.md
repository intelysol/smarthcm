# Webhook Handling & Asynchronous Payment Settlement

## 1. Webhook Flow

```
Payment Provider (Stripe / Bank)
               ↓
POST /api/v1/billing/webhooks/{provider}
               ↓
Signature Verification (HMAC-SHA256)
               ↓
Idempotency Audit (Event ID check)
               ↓
Database Transaction (billing_payments / billing_invoices update)
               ↓
Emit Billing Event & Settle Subscription
```

---

## 2. Signature Verification

Incoming webhook requests must include the provider signature header (`Stripe-Signature` or `X-Webhook-Signature`).
- The secret key is resolved from `billing_provider_accounts.config.webhook_secret`.
- Unsigned or tampered payloads immediately terminate with `400 Invalid webhook signature`.

---

## 3. Supported Events

| Provider Event | System Action |
|---|---|
| `payment_intent.succeeded`, `charge.succeeded` | Resolves invoice from metadata, records `succeeded` payment, zeroes out `balance_due`, sets invoice to `paid`, and reactivates subscription if past due |
| `charge.failed` | Records `failed` payment with failure reason, marks subscription `past_due`, and begins grace period |
| `customer.subscription.deleted` | Cancels tenant subscription gracefully |
