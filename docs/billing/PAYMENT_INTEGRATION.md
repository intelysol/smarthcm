# Payment Integration & Gateway Abstraction

## 1. Provider-Neutral Architecture

The billing system communicates through `PaymentProviderInterface`:
```text
PaymentManager
      ├── MockPaymentProvider (Test & offline environments)
      ├── StripeSandboxProvider (Stripe API & Webhooks)
      └── Future Providers (PayPal, Bank Wire, Local Escrow)
```

No core billing model references vendor-specific SDK classes directly.

---

## 2. Tokenization & PCI Compliance

- **Zero Card Storage**: Raw credit card numbers, CVVs, and magnetic stripe data are **NEVER** stored in the database or logged in application logs.
- Transactions are initiated with payment method tokens (e.g. `tok_visa`, `pm_12345`).
- Succeeded charges store only the provider's `provider_transaction_id` and masked payment details.

---

## 3. Refunds & Adjustments

- Refunds are tracked in `billing_refunds`, referencing the original `payment_id` and `invoice_id`.
- Succeeded refunds adjust the invoice's `amount_paid` and `balance_due`, updating the payment status to `partially_refunded` or `refunded`.
