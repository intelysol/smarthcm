# Commercial Billing & Gateway Reconciliation

## 1. Objective

Automated reconciliation prevents revenue leakage by verifying consistency between:
1. Internal commercial invoices (`billing_invoices`)
2. Payment ledger transactions (`billing_payments`)
3. Gateway settlement confirmations (`provider_transaction_id`)

---

## 2. Discrepancy Classifications

| Discrepancy Type | Description |
|---|---|
| `amount_mismatch` | Invoice records a different `amount_paid` than the verified sum of succeeded payment transactions |
| `missing_payment` | Invoice is marked `paid` or `partially_paid`, but no succeeded payment transactions exist in the database |
| `orphan_transaction` | A successful charge was confirmed by the payment gateway, but has no corresponding invoice |
| `failed_settlement` | Gateway transaction succeeded, but the invoice balance was not updated |

---

## 3. Audit & Resolution Workflow

- **Automated Audit**: The scheduled job `billing:reconciliation-audit` runs daily at 03:30 UTC via `BillingReconciliationService::auditDaily()`.
- **Administrative Command Center**: Discrepancies appear in the Administrative Billing Center (`/operations/billing`).
- **Resolution**: An authorized financial officer resolves the issue with notes, recording `resolved_by` and `resolved_at` in `billing_reconciliation_records`.
