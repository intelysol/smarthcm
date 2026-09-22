# Enterprise Integration & Webhook Security

## 1. Integration Trust Model

Third-party integrations, external webhooks, and partner API connections introduce perimeter trust boundaries. All inbound and outbound integration data exchange is protected through cryptographic signatures, strict replay mitigation, and idempotency tracking.

---

## 2. Inbound Webhook Security (`WebhookSecurityService`)

Inbound webhooks (e.g. Stripe/Billing, HRIS sync, external LMS, background check providers) are processed through `App\Domains\Integration\Services\WebhookSecurityService`:

### 2.1 Cryptographic Signature Verification
- Inbound webhooks must carry a cryptographic HMAC signature in designated headers:
  `X-SmartHCM-Signature`, `X-Hub-Signature-256`, or `X-Signature-SHA256`.
- Signatures are computed over the raw request payload:
  `HMAC-SHA256(timestamp + "." + raw_payload, tenant_webhook_secret)`
- Verification uses constant-time string comparison (`hash_equals`) to prevent timing attacks.
- Missing, empty, or mismatched signatures are rejected immediately with `401 Unauthorized`.

### 2.2 Replay Attack Prevention
- Incoming webhooks must supply a timestamp header (`X-SmartHCM-Timestamp` or standard epoch timestamp).
- The service validates that the timestamp does not exceed the maximum allowed drift window (default: **300 seconds / 5 minutes**).
- Requests with timestamps older than 5 minutes or skewed into the future are rejected with `400 Bad Request`.

### 2.3 Idempotency Key Enforcement
- Webhook payloads carry an `idempotency_key` or `delivery_id`.
- The service records processed idempotency keys in `api_idempotency_keys` with a 24-hour TTL.
- Duplicate payloads return the previously cached response status without re-executing domain actions, preventing duplicate credit processing, leave approvals, or employee provisioning.

---

## 3. Outbound Webhook Security

When the platform disseminates domain events (e.g. `employee.created`, `timesheet.approved`, `payroll.finalized`) to subscriber endpoints:

1. **HMAC Signing:** Outbound payloads are signed using the subscriber's private secret via `X-SmartHCM-Signature: sha256={hash}` and include `X-SmartHCM-Timestamp`.
2. **Payload Sanitization:** Highly sensitive PII (bank account numbers, tax identifiers, medical records) are stripped or masked prior to dispatch, in accordance with the tenant's data classification policy.
3. **Delivery Retries & Circuit Breaker:** Failed deliveries retry with exponential backoff (1m, 5m, 15m, 1h). Endpoints with repeated failures (10+ consecutive 5xx errors) enter a temporary tripped state to prevent resource starvation.

---

## 4. API Credential & Secret Management

- **Storage:** Third-party OAuth tokens, client secrets, and API credentials are encrypted at rest using `Illuminate\Support\Facades\Crypt` (AES-256-GCM).
- **Masking:** Secrets are never returned in cleartext via API or UI after initial generation. Display interfaces show masked tokens (e.g. `whsec_...9b2a`).
- **Rotation:** Supported automated key rotation with overlap grace periods (dual-key verification window) to ensure zero downtime during credential renewals.
