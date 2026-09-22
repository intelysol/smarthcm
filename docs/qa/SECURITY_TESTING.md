# Platform Security Testing, Threat Modeling & Invariants

## 1. Security Architecture Overview

The Enterprise Application Platform enforces defense-in-depth across authentication, authorization, session life-cycles, input sanitization, file uploads, and AI autonomy governance.

---

## 2. Core Security Pillars Tested

### A. Authentication & Session Security (`tests/Security/AuthenticationTestSuiteTest.php`)
- **Brute Force Defense**: Rate limiting throttles brute force attempts with HTTP 429 Too Many Requests.
- **Credential Validation**: Timing-safe comparison; rejection of incorrect passwords and disabled accounts.
- **Session Termination**: Logout strictly revokes API bearer tokens and invalidates server-side session cookies.
- **Suspended Tenant Lockout**: Users belonging to tenants with status `suspended` or `terminated` are immediately blocked from logging in.

### B. Authorization & Least Privilege (`tests/Security/AuthorizationTestSuiteTest.php`)
- **Strict Role Boundaries**: Regular employees attempting administrative routes (`/portal/admin/...`) are rejected with `403 Forbidden` or redirected.
- **Privilege Escalation Prevention**: An employee cannot grant themselves manager or tenant admin permissions.
- **Tenant Context Verification**: An authenticated user cannot access data outside their assigned tenant.

### C. Injection & File Safety (`tests/Security/SecurityRegressionSuiteTest.php`)
- **SQL Injection**: Input parameters (queries, filters, sorts) are strictly parameterized. Malicious SQL strings (`' OR 1=1; DROP TABLE...`) are escaped safely without generating SQL errors.
- **Path Traversal**: File path parameters containing `../`, `..%2F`, or encoded directory traversals are rejected with `400` / `404`.
- **Executable Script Uploads**: File uploads enforce strict MIME type and extension whitelists. Files with extensions `.php`, `.exe`, `.sh`, `.phtml` are rejected at the edge.
- **Cross-Site Scripting (XSS)**: User inputs in notes, comments, and biographies are sanitized and safely JSON encoded.

### D. AI Governance & Autonomy Boundaries (`tests/Security/AiGovernanceSafetyTest.php`)
- **Human-in-the-Loop (HITL)**: AI Concierge actions (e.g. submitting leave requests) start in `PROPOSED` status and require explicit human confirmation.
- **Non-Autonomous Adverse Actions**: AI is strictly prohibited from autonomously executing destructive or adverse employment actions (e.g. employee termination, salary reductions, disciplinary sanctions).
- **Cross-Tenant Context Protection**: AI concierge prompts requesting other tenants' confidential data are blocked from accessing cross-tenant vector context.

---

## 3. Continuous Security Testing Automation

Security test suites run on every commit and pull request as part of the pre-merge gate:

```bash
php artisan test tests/Security
```

Any failure in `tests/Security` halts CI immediately and blocks deployment certification.
