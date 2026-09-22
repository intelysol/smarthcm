# Enterprise Platform — Structured Logging & Observability Standard

## 1. Core Principles
1. **Machine-Readable Structure:** All production logs must be emitted as single-line JSON objects with ISO 8601 timestamps, log level, channel, and contextual keys.
2. **Mandatory Context:** Every log entry originating from an HTTP request or asynchronous job must contain correlation metadata:
   - `request_id`: Traces a user action from reverse proxy to database query.
   - `correlation_id`: Multi-hop identifier spanning micro-integrations and webhook relays.
   - `tenant_id`: Guarantees audit segregation in multi-tenant environments.
   - `user_id`: Authenticated actor UUID/ID (null for anonymous/system events).
3. **Zero Secret Leakage:** Sensitive parameters are automatically scrubbed via `StructuredJsonFormatter`.

---

## 2. Log Envelope Specification
```json
{
  "timestamp": "2026-09-14T11:45:00.123456+00:00",
  "level": "INFO",
  "message": "Employee record updated successfully",
  "channel": "daily",
  "request_id": "req_550e8400-e29b-41d4-a716-446655440000",
  "tenant_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "user_id": 42,
  "context": {
    "employee_id": 1084,
    "changes": ["department_id", "title"],
    "ip": "192.168.1.100",
    "uri": "/api/v1/employees/1084",
    "method": "PATCH"
  },
  "extra": {}
}
```

---

## 3. Prohibited Log Content (Zero-Tolerance)
Under no circumstances may the following data types appear in application logs:
- Plaintext passwords or password reset tokens.
- Bearer tokens, OAuth access/refresh tokens, or API keys.
- Cryptographic private keys or shared HMAC secrets.
- Full credit card numbers, CVVs, or bank account PINs.
- Unredacted national identity numbers (SSN, National ID).
- Private document contents or biometric template blobs.

---

## 4. Log Levels & Usage Guidelines
- **`EMERGENCY`**: System is unusable (Database completely unreachable, disk 100% full).
- **`ALERT`**: Action must be taken immediately (Corruption detected, payment provider offline).
- **`CRITICAL`**: Critical condition (Dead-letter queue spike, authentication security breach).
- **`ERROR`**: Runtime errors that do not require immediate intervention but should be monitored (External API 5xx, failed job exhausted retries).
- **`WARNING`**: Exceptional occurrences that are not errors (Rate limit threshold reached, deprecation).
- **`NOTICE`**: Normal but significant events (Tenant onboarded, user role promoted).
- **`INFO`**: Interesting business events (Employee created, payroll batch completed, login).
- **`DEBUG`**: Detailed debug information (Disabled in production).
