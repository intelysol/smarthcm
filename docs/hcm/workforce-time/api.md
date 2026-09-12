# API Reference — Epic 2.47 Workforce Time

## Base Prefix
`/api/v1/hcm/time`

## Endpoints
| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/punch` | Ingest punch event with idempotency |
| `GET` | `/corrections` | List attendance correction requests |
| `POST` | `/corrections` | Submit attendance correction request |
| `POST` | `/corrections/{id}/approve` | Approve correction and recalculate session |
| `POST` | `/corrections/{id}/reject` | Reject correction with reason |
| `POST` | `/allocations` | Allocate project/task time on timesheet |
| `GET` | `/timesheets/{id}/allocations/summary` | Get project and cost-center allocation breakdown |
| `GET` | `/overtime-tiers` | List overtime tier records |
| `POST` | `/overtime-tiers/calculate` | Calculate multi-tier overtime for daily work |
| `POST` | `/overtime-tiers/{id}/approve` | Approve overtime tier record |
| `POST` | `/overtime-tiers/{id}/reject` | Reject overtime tier record |
| `GET` | `/compliance-checks` | List labor compliance checks |
| `POST` | `/compliance-checks/scan/{sessionId}` | Scan attendance session for compliance violations |
| `POST` | `/compliance-checks/{id}/waive` | Waive compliance check with reason |
| `GET` | `/payroll-exports` | List batched payroll exports |
| `POST` | `/payroll-exports` | Generate batched payroll export payload |
| `POST` | `/payroll-exports/{id}/dispatch` | Dispatch payload to Integration Hub |
| `GET` | `/payroll-reconciliations` | List payroll reconciliation runs |
| `POST` | `/payroll-reconciliations` | Reconcile export against payroll processed records |
| `GET` | `/ai/anomalies` | Advisory attendance anomaly analysis |
| `GET` | `/ai/timesheet-variance/{timesheetId}` | Advisory timesheet explanation & variance drivers |