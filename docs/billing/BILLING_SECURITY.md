# Commercial Billing Security & Tenant Isolation

## 1. Multi-Tenant Scoping Standard

Every tenant-facing commercial query must filter on `tenant_id`.
```php
// Standard pattern
$invoice = BillingInvoice::where('tenant_id', $tenantId)->findOrFail($id);
```
- Prohibits cross-tenant IDOR (Insecure Direct Object Reference).
- Verified by automated tests in `tests/Security/BillingTenantIsolationTest.php`.

---

## 2. Commercial Fraud & Abuse Controls

1. **Strict Webhook HMAC Verification**:
   All incoming provider calls are cryptographically signed using SHA-256 HMAC and compared with timing-attack resistant `hash_equals()`.
2. **Idempotency Keys**:
   Usage metering prevents duplicate credit deductions or duplicate charges via unique key deduplication.
3. **Credit & Discount Protection**:
   Coupon redemptions validate date windows, status flags, and `max_redemptions` caps atomically in database transactions.
4. **Data Redaction**:
   No raw card data, CVVs, or payment tokens are logged in structured JSON logs or stored in plaintext.
