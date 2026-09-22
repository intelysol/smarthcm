# Billing Operations Runbook & Scheduled Automations

## 1. Scheduled Background Jobs

Configured in `routes/console.php`:

| Schedule | Frequency | Job / Command | Description |
|---|---|---|---|
| `01:30 UTC` | Daily | `billing-renewals-and-invoicing` | Renews active subscriptions ending in $\le 24$h and generates invoices |
| `Hourly` | Hourly | `billing-usage-aggregation` | Pre-aggregates usage into daily snapshots and checks 80%/90%/100% threshold limits |
| `03:30 UTC` | Daily | `billing-reconciliation-audit` | Audits settled invoices against payment records for discrepancies |

---

## 2. On-Demand CLI Commands

```bash
# Process pending subscription renewals and generate invoices
php artisan billing:renewals:process

# Run commercial reconciliation audit
php artisan billing:reconciliation:audit
```

---

## 3. Administrative Incident Playbooks

- **Handling a Failed Payment**:
  1. The system automatically marks the invoice `past_due` and initiates a 7-day grace period.
  2. If payment is not received before `grace_ends_at`, the system transitions the subscription to `suspended`.
  3. During suspension, data is preserved but non-essential module access is blocked via `EnforceCommercialEntitlement`.
  4. Once payment succeeds, the subscription is automatically returned to `active`.
