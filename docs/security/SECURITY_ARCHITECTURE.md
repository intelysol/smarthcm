# Enterprise Security Architecture & Defense-in-Depth

## 1. Executive Summary

The Enterprise Application Platform adheres to a multi-tiered **Defense-in-Depth** and **Zero-Trust** security architecture. Every incoming request—regardless of network origin, protocol (HTTP/REST, Inertia, WebSocket, GraphQL, CLI, Queue/Job), or user authentication state—is subject to independent, continuous identity, tenancy, and authorization validation.

Under this model:
> **Authentication ≠ Authorization.**  
> Being an authenticated user in the system grants zero default access to any data or capability. Every sensitive operation must prove:  
> `WHO` → `WHAT TENANT` → `WHAT ROLE` → `WHAT PERMISSION` → `WHAT RESOURCE` → `WHAT ACTION` → `WHAT BUSINESS STATE` → `WHAT APPROVAL` → `WHAT AUDIT TRAIL`.

---

## 2. Platform Trust Boundaries & Layered Defenses

```text
[ Internet / External Clients ]
              │
              ▼  (Layer 1: Network & Gateway Perimeter)
   ┌─────────────────────────────────────────────────────────────────┐
   │ TLS 1.3 Termination, DDoS Mitigation, Rate Limiting (IP/Route)  │
   │ WAF (ModSecurity/Cloudflare), Geo-Fencing, Header Validation    │
   └─────────────────────────────────────────────────────────────────┘
              │
              ▼  (Layer 2: Application Ingress & Session Security)
   ┌─────────────────────────────────────────────────────────────────┐
   │ Correlation ID (X-Request-ID), Security Headers (CSP, HSTS, XFO)│
   │ CSRF Token Validation, Secure SameSite Session Cookies          │
   └─────────────────────────────────────────────────────────────────┘
              │
              ▼  (Layer 3: Identity & Tenant Context Resolution)
   ┌─────────────────────────────────────────────────────────────────┐
   │ Authentication (Session / Bearer Token / API Key / mTLS)        │
   │ ResolveTenant Middleware -> RequestTenantContext (Immutable)    │
   │ Strict Multi-Tenant Isolation (Cross-tenant spoofing blocked)   │
   └─────────────────────────────────────────────────────────────────┘
              │
              ▼  (Layer 4: Access Control & Authorization Plane)
   ┌─────────────────────────────────────────────────────────────────┐
   │ AuthorizationService: RBAC + PBAC + ABAC                        │
   │ Explicit Deny -> Explicit Allow -> Direct Perm -> Role Perm     │
   │ Domain Policies (belongsToSameTenant, Resource Ownership)       │
   └─────────────────────────────────────────────────────────────────┘
              │
              ▼  (Layer 5: Domain Services & Business Logic)
   ┌─────────────────────────────────────────────────────────────────┐
   │ Input Validation (FormRequests, DTOs, Type Safety)              │
   │ Business State Validation, Concurrency & Idempotency Locks      │
   │ Safe Parameter Binding, Mass-Assignment Protection              │
   └─────────────────────────────────────────────────────────────────┘
              │
              ▼  (Layer 6: Persistence & Storage Isolation)
   ┌─────────────────────────────────────────────────────────────────┐
   │ Database Scoping: BelongsToTenant Eloquent Global Scopes        │
   │ Isolated S3/Storage Paths per Tenant (tenants/{id}/...)         │
   │ Sensitive Field Encryption at Rest (AES-256-GCM)                │
   └─────────────────────────────────────────────────────────────────┘
              │
              ▼  (Layer 7: Observability, Audit & Incident Telemetry)
   ┌─────────────────────────────────────────────────────────────────┐
   │ Cryptographic AuditService (SHA-256 integrity hash chains)      │
   │ Sensitive Data Masker in Logs, SIEM / SecOps Alerting           │
   └─────────────────────────────────────────────────────────────────┘
```

---

## 3. Defense-in-Depth Control Matrix

| Layer | Primary Security Mechanism | Threat Addressed | Verification Control |
|---|---|---|---|
| **L1: Edge** | Rate Limiting, TLS 1.3, Geo-Fencing | Volumetric DDoS, Brute Force, Eavesdropping | Load Balancer Rules & Fail2Ban |
| **L2: Ingress** | CSP, XFO, HSTS, CSRF Tokens, Correlation IDs | XSS, Clickjacking, Replay, Session Hijacking | `SecurityHeadersMiddleware`, CSRF Verify |
| **L3: Identity** | Password Hashing (Argon2id/Bcrypt), MFA, `TenantContext` | Credential Stuffing, Tenant Spoofing, Session Fixation | `ResolveTenant`, MFA Guard |
| **L4: Authorization** | `AuthorizationService`, Gates, Domain Policies | Privilege Escalation, BOLA/IDOR, Lateral Movement | `EmployeePolicy`, `BasePolicy`, RBAC Tests |
| **L5: Domain** | Strongly-typed DTOs, FormRequests, Mass Assignment Guard | Parameter Tampering, SQL Injection, Logic Flaws | FormRequest Rules, Entity DTOs |
| **L6: Storage** | `BelongsToTenant` Global Scope, Storage Isolation | Cross-Tenant Data Leaks, Unauthenticated Downloads | `TenantIsolationTest`, Signed URLs |
| **L7: Audit** | Tamper-Evident Audit Trails, Sensitive Masker | Repudiation, Insider Threats, Credential Leakage in Logs | `AuditService`, `SensitiveDataMasker` |

---

## 4. Architectural Boundaries & Safe Failures

1. **Fail-Closed Default:** If tenant resolution fails, authorization lookup encounters a cache error, or a permission cannot be confirmed, the system immediately rejects the request with `401 Unauthorized` or `403 Forbidden`.
2. **Context Immutability:** Once a tenant context is resolved by `RequestTenantContext`, it cannot be changed or swapped within that HTTP request lifecycle, preventing race-condition tenant hopping.
3. **No Unsanitized Exceptions:** Production exception rendering strictly suppresses SQL queries, file paths, connection credentials, and stack traces. All errors return a standardized JSON envelope with a tracking `request_id`.
