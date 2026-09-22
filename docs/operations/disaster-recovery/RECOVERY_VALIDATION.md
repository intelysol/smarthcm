# Post-Recovery Validation & Data Integrity Checklist

## 1. Multi-Layer Recovery Verification Gates
Before releasing a recovered environment to production traffic, the following seven validation gates must be certified with formal audit sign-offs:

```text
[Gate 1: Infrastructure & Probes]  ──→ /health/live (200), /health/dependencies (OK)
[Gate 2: Schema Integrity]         ──→ Table count, foreign keys, index listing
[Gate 3: Relational Data Integrity]──→ Zero orphan records, PK continuity, audit linkages
[Gate 4: Tenant Boundary Isolation]──→ Zero cross-tenant record leakage
[Gate 5: Document Checksums]       ──→ File existence and SHA-256 match
[Gate 6: Core Domain Smoke Tests]  ──→ Auth, Payroll, Leave, Attendance, Billing
[Gate 7: Observability Restoration]──→ Correlated logging, metric recording, alerts active
```

---

## 2. Gate Verification Execution Checklist

### Gate 1: Infrastructure & Health Probes
```bash
curl -s http://127.0.0.1:8000/health/live | jq .status
# Expected: "ok"
curl -s http://127.0.0.1:8000/health/ready | jq .ready
# Expected: true
curl -s http://127.0.0.1:8000/health/dependencies | jq .status
# Expected: "ok"
```

### Gate 2: Schema Integrity
- Verify 985 tables exist.
- Verify 2,180 foreign key constraints are established without cascade corruption.
- Confirm column schemas match authoritative migrations.

### Gate 3: Relational Integrity & Orphan Auditing
- Verify that every employee record references a valid tenant and department.
- Verify that every attendance punch and payroll item references an existing employee record.
- Verify that every approval step references a valid workflow instance.

### Gate 4: Tenant Boundary Isolation
- Verify Tenant A credentials cannot retrieve Tenant B employee records.
- Verify Tenant A cannot access Tenant B private documents.
- Verify database queries automatically apply tenant scoping.

### Gate 5: Document & Artifact Verification
- Random sample 50 document records across tenants.
- Verify file existence on disk / S3 bucket.
- Verify computed SHA-256 hash matches the checksum recorded in the database.

### Gate 6: Domain Smoke Tests
- Employee login test.
- Payroll calculation idempotency assertion.
- Leave request submission and approval workflow progression.
