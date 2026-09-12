# Cost Snapshots, Versioning & Immutability

## Snapshot Architecture
Cost snapshots (`HcmWorkforceCostSnapshot`) freeze workforce cost intelligence for a given calendar period:
- **Versioning**: If adjustments occur post-lock, a new version (e.g. `version = 2`) is generated without mutating the previous snapshot.
- **Idempotency Key**: Generated as `md5(tenant_id + period_name + version)`.
- **Metrics Stored**: Total cost, direct labor, indirect labor, burden, overtime, benefits, contractor spend, absence, vacancy, headcount, and total FTE.