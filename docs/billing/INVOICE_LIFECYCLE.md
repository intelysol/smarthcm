# Invoice Lifecycle & Line-Item Preservation

## 1. Invoice Lifecycle State Machine

```
   [DRAFT]
      ↓
   [ISSUED] ──(partial payment)──→ [PARTIALLY_PAID]
      ↓                                    ↓
    [PAID] ←───────────────────────────────┘
      │
(if due date passes without full settlement)
      ↓
  [PAST_DUE] ──(unrecoverable)──→ [UNCOLLECTIBLE] / [VOID]
```

---

## 2. Line Item Price Preservation

Invoices must reflect the exact commercial terms contracted at the moment of billing:
- Each `billing_invoice_items` record stores a fixed snapshot of `unit_price`, `quantity`, `amount`, and `description`.
- Subsequent changes to base plan prices or seat tiers do NOT alter historical invoices.

---

## 3. Automated Offsets & Tax Computation

When an invoice is issued:
1. **Subtotal**: Sum of plan base fee, provisioned seat tiers, and metered overages.
2. **Promotional Discounts**: Valid coupon codes apply percentage or fixed reductions.
3. **Tax Computation**: The tenant's jurisdiction is resolved from `billing_tax_records` and added.
4. **Credit Wallet Application**: Any positive `billing_credits` balance is automatically deducted from `balance_due` and recorded in `billing_credit_transactions`.
