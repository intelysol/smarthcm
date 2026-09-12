# Security, RBAC & Isolation Standards

## 1. Multi-Tenant Isolation
All tables in the Workforce Administration module (`hcm_ops_*`) enforce a mandatory `tenant_id` column and index. Database queries utilize tenant-scoped query filters to prevent cross-tenant data leakage.

## 2. Fine-Grained Permissions
- `hcm.operations.view`: Read-only access to operational cockpit and queues.
- `hcm.operations.manage`: Ability to create queues, assign items, and triage exceptions.
- `hcm.operations.exceptions.manage`: Ability to investigate and resolve operational exceptions.
- `hcm.operations.bulk.view`: View bulk operation plans and dry-run summaries.
- `hcm.operations.bulk.create`: Create draft bulk operations.
- `hcm.operations.bulk.execute`: Execute approved bulk operations (requires elevated authorization).
- `hcm.operations.governance.manage`: Configure governance policies, rules, and SLA targets.
- `hcm.operations.data_quality.view`: Access data quality scores and scan results.
- `hcm.operations.reconciliation.view`: Run and view cross-domain reconciliation checks.
- `hcm.operations.configuration.view`: Access configuration health diagnostics.

## 3. Immutable Audit Logging
Critical operational state changes (bulk operation executions, checklist instantiations, exception status transitions) write immutable audit logs via `AuditService`.
