# Commercial Product & Plan Model

## 1. Hierarchy Overview

```text
Product (e.g. Enterprise HCM, Future CRM, Future ERP)
 └── Product Version (e.g. 2026.1)
      └── Plan (e.g. Starter, Professional, Enterprise Suite)
           ├── Price Components (Base Platform, Employee Seat, Modules)
           └── Plan Entitlements (employee_limit, payroll_enabled, ai_enabled)
```

---

## 2. Seeded Commercial Catalog

| Plan Code | Name | Base Price | Interval | Included Seats | Overage/Seat | Key Entitlements |
|---|---|---|---|---|---|---|
| `hcm-starter` | HCM Starter | $49.00 | Monthly | 10 | $5.00 | `core_hr_enabled`, `attendance_enabled`, 10 seats |
| `hcm-professional` | HCM Professional | $199.00 | Monthly | 50 | $4.00 | Starter + `payroll_enabled`, `recruitment_enabled`, 50 seats |
| `hcm-enterprise` | HCM Enterprise Suite | $599.00 | Monthly | 200 | $3.00 | Professional + `learning_enabled`, `performance_enabled`, `ai_concierge_enabled`, `advanced_analytics_enabled`, 200 seats |

---

## 3. Versioning & Immutability

- Plans maintain an integer `version` field.
- When commercial terms or pricing for an existing plan change, a new plan version or new plan code is created.
- Existing tenant subscriptions retain their contracted plan configuration and prices until explicit upgrade or migration.
