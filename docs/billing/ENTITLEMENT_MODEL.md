# Commercial Entitlement Model & Feature Enforcement

## 1. Permission vs. Entitlement Boundary

```text
RBAC Permission
= What the individual user's assigned role is authorized to perform.

Commercial Entitlement
= What the tenant organization has commercially purchased and provisioned.
```

An operation requires **both** to succeed:
```
User Request
    ↓
Tenant Active Subscription Valid?
    ↓
Tenant has Entitlement? (e.g. payroll_enabled)
    ↓
User has Role Permission? (e.g. payroll.manage)
    ↓
Operation Allowed
```

---

## 2. Supported Entitlement Types

1. **Boolean Features**:
   - `core_hr_enabled`
   - `attendance_enabled`
   - `payroll_enabled`
   - `recruitment_enabled`
   - `learning_enabled`
   - `performance_enabled`
   - `ai_concierge_enabled`
   - `advanced_analytics_enabled`
2. **Quantitative Quotas / Limits**:
   - `employee_limit` (Maximum active employee records)
   - `user_limit` (Maximum active tenant user seats)
   - `storage_limit_gb` (Document and asset storage capacity)
   - `api_limit_monthly` (API call quota per billing cycle)
   - `ai_token_limit_monthly` (Generative AI token quota)

---

## 3. Implementation & Middleware

- **Middleware**: `App\Domains\Platform\Http\Middleware\EnforceCommercialEntitlement`
  - Registered as `commercial.entitlement:feature_key`
  - Returns `403 COMMERCIAL_ENTITLEMENT_REQUIRED` with upgrade URL for API requests, or redirects to `/portal/billing` with an upgrade warning for browser requests.
- **Facade**: `App\Domains\Billing\Support\CommercialFacade` (`Billing::*`)
  - `Billing::entitled($tenantId, 'payroll_enabled')`
  - `Billing::canAddEmployee($tenantId)`
  - `Billing::getLimit($tenantId, 'employee_limit')`
