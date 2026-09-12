# Onboarding Security & Authorization

## Tenant Isolation
All onboarding tables enforce strict tenant filtering (`where('tenant_id', $tenantId)`). Cross-tenant access is strictly prohibited with an `AuthorizationException`.

## Scopes
- **Employee Self-Service**: Employees can only view and mutate tasks belonging to their own case.
- **Reporting Manager Scope**: Managers can only view onboarding progress for their direct reports.
- **HR Administrator**: Full administrative oversight across company onboarding cases.
