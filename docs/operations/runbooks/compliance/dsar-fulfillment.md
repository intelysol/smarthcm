# Operational Runbook: Data Subject Access Request (DSAR) Fulfillment

## Severity: P2 (Privacy Compliance & Legal SLA)
## SLA Deadline: 30 Calendar Days (GDPR Article 12)

### 1. Request Intake & Verification
1. DSAR request received via `/portal/privacy` or registered by Privacy Officer in `privacy_requests`.
2. Verify subject identity via Multi-Factor Authentication or verified Government ID upload.
3. Update `identity_verified = true` and advance status to `in_progress`.

### 2. Governed Cross-Domain Data Extraction
1. Execute governed data compilation across domains:
   ```bash
   php artisan privacy:dsar:compile --request-id={REQUEST_ID}
   ```
2. Extracted domain data includes:
   * Profile and contact information
   * Position, department, and employment history
   * Active and historical leave balances
   * Biometric attendance punch history
   * Document metadata and signed acknowledgements
3. Verify that the generated export JSON has a valid SHA-256 hash recorded in `export_hash_sha256`.

### 3. Review & Secure Delivery
1. Privacy Officer reviews export payload to ensure no third-party PII is inadvertently exposed.
2. Deliver download link to the employee with password-protected / authenticated access.
3. Update request status to `completed` and set `completed_at = now()`.
