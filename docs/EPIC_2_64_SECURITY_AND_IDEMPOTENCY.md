# Epic 2.64 — Security, Idempotency & Resiliency Specification

## 1. Webhook Security Specification

### HMAC-SHA256 Signatures
All inbound webhooks are validated by `WebhookSecurityService`:
```
signature = "sha256=" + hash_hmac("sha256", rawPayload, sharedSecret)
```
- Constant-time comparison via `hash_equals()` protects against timing attacks.
- Header options: `X-Hub-Signature-256`, `X-Webhook-Signature`, or custom tenant-configured headers.

### Timestamp Skew & Replay Window
- Inbound webhooks must supply a timestamp header (`X-Webhook-Timestamp`).
- Requests with timestamps deviating more than **300 seconds (5 minutes)** from server time are rejected with `401 Unauthorized` (`TIMESTAMP_OUT_OF_BOUNDS`).

---

## 2. Inbound & Outbound Idempotency Safeguards

### Inbound Webhook Replay Suppression
1. Every incoming webhook requires an event ID (`X-Event-ID` or payload `event_id`).
2. The event ID is recorded in `api_idempotency_keys` with the target resource identifier and status `completed`.
3. If an identical event arrives again:
   - The system intercepts the duplicate before invoking domain services or database writes.
   - It returns HTTP `200 OK` with `{"status": "already_processed", "idempotent": true}`.
   - **Verification:** Proven in automated test `EndToEndDemoIntegrationTest::test_duplicate_webhook_is_suppressed_idempotently()`.

### Outbound Dispatch Idempotency
- Each outbound delivery event generates a unique UUID `delivery_token`.
- External partners receiving webhooks are provided this token in `X-Delivery-Token` to deduplicate retries on their end.

---

## 3. Asynchronous Resiliency & Dead Letter Queue (DLQ)

### Outbound Retry Policy
`OutboundIntegrationJob` and `WebhookDeliveryJob` implement an exponential backoff schedule with jitter:
- Attempt 1: Immediate
- Attempt 2: 15 seconds
- Attempt 3: 60 seconds
- Attempt 4: 300 seconds (5 minutes)
- Attempt 5: 900 seconds (15 minutes)

### Dead-Letter Isolation
When all retries are exhausted or an unrecoverable business exception occurs:
1. `DeadLetterService::record()` creates an immutable record in `integration_dead_letters`.
2. Captured fields:
   - `connection_id`: Source or target integration connection
   - `payload`: Original raw payload
   - `error_message`: Full exception message
   - `stack_trace`: Formatted stack trace
   - `status`: `pending`
3. Dead letters are monitored through the Integration Command Center UI.
4. Administrators can manually retry dead letters or request AI-assisted diagnosis.

---

## 4. AI-Powered Error Diagnosis & Schema Mapping
- `IntegrationAiAssistant` interfaces directly with the platform `AiGatewayService` (no duplicate AI engine created).
- **Error Diagnosis:** Analyzes raw payload and exception trace to determine root causes (e.g. data format mismatch, missing foreign key, network timeout) and recommends concrete remediation steps.
- **Mapping Suggestion:** Examines sample third-party JSON schemas and suggests canonical field mappings into the HCM domain format.
