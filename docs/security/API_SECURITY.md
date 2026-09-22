# Enterprise API Security & Endpoint Hardening

## 1. Overview & API Security Inventory

All REST and JSON API endpoints conform to strict enterprise security standards. No unauthenticated or unrestricted endpoints are exposed outside of designated public health/monitoring and authentication routes.

### API Security Inventory Standards:
Every API endpoint is cataloged and enforced with:
1. **Authentication:** Bearer token, session-based cookie, or signed API key.
2. **Tenant Scoping:** Explicit or implicit binding to `TenantContext`.
3. **Permission Check:** Evaluated via `hasPermission()`, Gate, or Policy.
4. **Rate Limiting:** Granular throttling per user, per IP, and per API token.
5. **Input Validation:** Strict `FormRequest` rules rejecting unexpected fields.
6. **Mass Assignment Protection:** Sensitive attributes guarded against client overwrites.
7. **Error Masking:** Unified error response envelope concealing stack traces and SQL diagnostics.

---

## 2. Mass Assignment & Parameter Tampering Defense

The platform enforces mass-assignment protection at both the Request and Eloquent levels:
- **Never rely on `$request->all()`:** Controllers must only use `$request->validated()` from dedicated `FormRequest` classes.
- **Guarded Sensitive Columns:**
  - `tenant_id`
  - `is_platform_admin`
  - `status` / `employment_status`
  - `role_id` / `permissions`
  - `approved_by` / `verified_by`
  - `base_salary` / `compensation`
- Client payloads containing these keys are automatically discarded or trigger validation errors (`422 Unprocessable Entity`).

---

## 3. IDOR / BOLA Prevention Architecture

Insecure Direct Object References (IDOR) and Broken Object-Level Authorization (BOLA) are mitigated via a 3-point validation sequence in every resource controller:

```text
Incoming Request: GET /api/v1/employees/{id}
       │
       ▼
1. Authenticated User Check (user !== null, active)
       │
       ▼
2. Tenant Boundary Query:
   Model::where('tenant_id', $user->tenant_id)->where('id', $id)->firstOrFail()
   (Foreign tenant IDs automatically return 404 Not Found, leaking no existence data)
       │
       ▼
3. Resource Policy Check:
   Gate::authorize('view', $resource)
   (Verifies whether the user is owner, direct manager, or authorized HR specialist)
```

---

## 4. Rate Limiting & Throttling Matrix

| Endpoint Tier | Rate Limit (Standard) | Rate Limit (Burst) | Scope / Key |
|---|---|---|---|
| **Public Health (`/up`, `/health`)** | 120 req / min | 200 req / min | IP Address |
| **Authentication (`/login`, `/mfa`)** | 5 req / min | 10 req / min | IP + Username |
| **Password Reset / Recovery** | 3 req / 15 min | 5 req / 15 min | Email / IP |
| **Standard Core API (`/api/v1/*`)** | 60 req / min | 100 req / min | User ID / Token ID |
| **Sensitive Exports (`/reports/export`)**| 5 req / 10 min | 10 req / 10 min | User ID + Tenant ID|
| **AI Advisory Concierge** | 15 req / min | 25 req / min | User ID + Tenant ID|
| **Inbound Webhooks** | 300 req / min | 600 req / min | Remote Client IP |

---

## 5. Standardized Error Response Envelope

All API exceptions return the standardized envelope specified in `bootstrap/app.php`:

```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You do not have permission to access this resource.",
    "details": []
  },
  "request_id": "req_0190a123-4567-7890-abcd-ef0123456789"
}
```

This prevents information disclosure (database structure, stack traces, internal software versions) while providing a unique `request_id` for security auditing and log correlation.
