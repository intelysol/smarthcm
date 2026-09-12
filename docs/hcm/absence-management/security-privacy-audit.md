# Security, Audit Trail & Privacy Safeguards

## Multi-Tenant Isolation
Every table in the Absence domain enforces multi-tenancy through `tenant_id` scoping. Cross-tenant leakage is strictly prevented at the query and model level.

## Role-Based Access Control (RBAC)
- **Employees**: View own absence records and RTW progress.
- **Line Managers**: View operational impacts, shift coverage, and RTW operational restrictions (no clinical data).
- **HR Admins**: Full access to absence case governance, multi-way reconciliation, and analytics.
- **Health Practitioners**: Sole custodians of medical diagnoses in Epic 2.34 (`hcm_health_records`).

## Immutable Audit Trails
All creations, updates, and status transitions across events, RTW plans, and reconciliations record `created_by`, timestamps, and historical change logs.