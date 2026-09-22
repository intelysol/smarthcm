# Epic 2.64 — API Management Gateway & Governance Specification

## 1. Overview & Goals
The Enterprise HCM API Management Gateway provides secure, governed external access to HCM resources for third-party consumers, partners, and tenant integrations.

---

## 2. API Gateway Architecture & Middleware Pipeline

```
HTTP Request
     |
     v
[VerifyApiKey] -------------> Checks X-API-Key / Bearer against SHA-256/SHA-512 hash in api_keys
     |                        Validates api_clients status = 'active' and client IP allowlist
     v
[EnforceApiScopes] ---------> Asserts required scope (e.g. employees:read, payroll:sync)
     |
     v
[EnforceApiRateLimits] -----> Enforces rate limit policy (e.g., 60 req/min, burst 10)
     |                        Returns 429 Too Many Requests with Retry-After header
     v
[ApiIdempotencyMiddleware] -> Checks Idempotency-Key against api_idempotency_keys table
     |                        Suppresses duplicate mutations; caches original responses
     v
[LogApiRequests] -----------> Captures latency, status, IP, user-agent into api_request_logs
     |
     v
[Domain Controller] --------> Executes business logic and returns standardized ApiResponse
```

---

## 3. Security & Hashing Specifications
- **API Key Storage:** Plain API keys are never stored. The system hashes keys using `SHA-256` and `SHA-512` digests stored in `api_keys.key_hash`.
- **Key Format:** Keys are prefixed for identification (e.g., `shcm_live_...` or `fep_...`).
- **Secret Encryption:** Client secrets are encrypted at rest using AES-256-GCM via `app(CredentialManager::class)`.
- **IP Allowlisting:** Optional CIDR or IP list configured per client in `api_clients.ip_allowlist`.

---

## 4. Rate Limiting & Tier Policies
- Rate limits are stored in `api_rate_limit_policies` per client and endpoint.
- Backed by Laravel Cache (Redis or database cache) using atomic counters.
- When exceeded, the gateway emits:
  - HTTP `429 Too Many Requests`
  - Header `Retry-After: {seconds}`
  - Header `X-RateLimit-Limit: {max}`
  - Header `X-RateLimit-Remaining: 0`
  - JSON error response: `{"success": false, "error": {"code": "RATE_LIMIT_EXCEEDED", ...}}`

---

## 5. Idempotency & Replay Protection
- Mutating endpoints (`POST`, `PUT`, `PATCH`) support the `Idempotency-Key` HTTP header.
- Handled via `ApiIdempotencyMiddleware` and persisted to `api_idempotency_keys`.
- If an idempotency key is replayed while in flight:
  - HTTP `409 Conflict` (`IDEMPOTENCY_CONCURRENT_REQUEST`)
- If an idempotency key is replayed after completion:
  - Gateway short-circuits execution and returns the cached HTTP response code and body directly with header `X-Cache-Lookup: HIT`.

---

## 6. API Governance & OpenAPI Catalog
- **OpenAPI 3.1 Specification:** Dynamic catalog served at `/api/v1/catalog/openapi.json`.
- **Catalog Endpoints:** Available at `/api/v1/catalog/endpoints` for discovery by external developer portals.
- **Auditing & Metrics:** Live telemetry querying `api_request_logs` by status code, client, and time range.
