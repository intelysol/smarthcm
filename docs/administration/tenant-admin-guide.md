# Tenant Administrator Guide

The **Tenant Administrator** is the primary organizational manager for a specific enterprise customer tenant. This role possesses complete authority within their tenant boundary while having zero visibility into any other tenant's data.

---

## Primary Administrative Domains

### 1. Organization Architecture
- **Companies & Subsidiaries**: Manage enterprise legal entity details, addresses, and currency settings.
- **Departments & Cost Centers**: Define organizational units (`/admin/departments`), reporting hierarchies, and division structures.
- **Job Positions**: Establish standardized position catalogs (`/admin/positions`), job grades, and head count limits.

### 2. User & Access Administration (`/admin/users`)
- **Add New Users**: Provision accounts for company staff with pre-selected roles (`tenant_admin`, `hr_admin`, `manager`, `employee`).
- **Status Lifecycle**: Instantly toggle employee login permissions (`Active` &harr; `Inactive`). Inactive users cannot authenticate or access any system APIs.
- **Password Resets**: Safely issue temporary passwords for locked-out employees.

### 3. Policies & Compliance Governance
- **Compliance Frameworks**: Manage ISO 27001, SOC 2, and GDPR controls (`/compliance/dashboard`).
- **Data Retention**: Configure automated archival policies for old audit records and payroll archives (`/admin/data-lifecycle`).
- **Document Templates**: Create company contracts, offer letters, and policy acknowledgment documents (`/employee-documents/admin/templates`).

---

## Tenant Boundary Security

All tenant admin actions are automatically scoped to the user's `tenant_id`. Any attempt to view, modify, or delete resources belonging to another tenant will result in an immediate `404 Not Found` or `403 Forbidden` response.