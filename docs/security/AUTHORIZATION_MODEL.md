# Enterprise Authorization Architecture & Access Control

## 1. Access Control Model

The Enterprise Application Platform implements a hybrid **RBAC + ABAC + PBAC** (Role-Based, Attribute-Based, and Policy-Based Access Control) engine coordinated by `App\Domains\Platform\Services\AuthorizationService`.

```text
               User Identity + Tenant Context
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│               Evaluation Hierarchy Pipeline                 │
├─────────────────────────────────────────────────────────────┤
│ 1. Explicit Denial (permission_overrides: 'deny')            │ ──► [ DENY 403 ]
│ 2. Explicit Allow (permission_overrides: 'allow')           │ ──► [ ALLOW 200 ]
│ 3. Direct User Permission (user_permissions)                │ ──► [ ALLOW 200 ]
│ 4. Active Tenant Role (user_roles + role_permissions)       │ ──► [ ALLOW 200 ]
│ 5. Legacy Role Assignment (role_user + permissions)         │ ──► [ ALLOW 200 ]
│ 6. Default Fallback                                         │ ──► [ DENY 403 ]
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Decision Pipeline Rules

1. **Explicit Deny Precedence:** If an explicit override rule with effect `deny` is present for `(tenant_id, user_id, permission_name)`, access is immediately denied regardless of any attached roles.
2. **Effective Window Scoping:** Overrides and role assignments support `effective_from` and `effective_to` timestamps. Expired assignments are automatically bypassed during evaluation.
3. **Cache Invalidation & Versioning:**
   - Authorization decisions are cached for up to 5 minutes using composite keys:
     `authz:{tenantId}:{userId}:{version}:{sha1(permission)}`
   - Any role assignment, revocation, or permission grant automatically increments the user's authorization version (`authz:version:{tenantId}:{userId}`), instantly invalidating all cached decisions across cluster nodes.

---

## 3. Resource-Level Policies & Attribute-Based Constraints

Backend policies extend `App\Domains\Shared\Policies\BasePolicy` to enforce multi-dimensional constraints:

```php
abstract class BasePolicy
{
    protected function belongsToSameTenant(User $user, Model $model): bool
    {
        return isset($model->tenant_id) 
            && (string) $user->tenant_id === (string) $model->tenant_id;
    }
}
```

### 3.1 Scope Dimensions
- **Tenant Scope:** The resource MUST belong to the user's active tenant (`user->tenant_id === model->tenant_id`).
- **Owner Scope:** Employees can view/edit their own resources (e.g. self-service leave requests, timesheets, profile address) if `employee_visible` is true.
- **Manager Scope:** Managers can review and approve resources belonging only to direct or indirect reports in their organizational reporting chain.
- **Department / Location Scope:** HR coordinators may have permissions restricted to a specific Business Unit, Division, or Geographic Branch.
- **Confidentiality Scope:** Sensitive documents marked `HR_CONFIDENTIAL` or `HIGHLY_RESTRICTED` require explicit elevated permissions (e.g. `employee_documents.view_er` or `view_compensation`) even if the user has broad employee management roles.

---

## 4. Frontend vs. Backend Authorization Boundary

| Responsibility | Frontend (React / Blade / Inertia) | Backend (API, Controllers, Services) |
|---|---|---|
| **Role** | Pure User Experience (UX) optimization | **Authoritative Security Enforcement** |
| **Actions** | Show/hide navigation menus, disable buttons | Reject unauthorized requests with `403 Forbidden` |
| **Trust Level** | **Completely Untrusted** | Authoritative Source of Truth |
| **Bypass Resistance**| User can tamper with DOM / client state | Cannot be bypassed; evaluated on server before execution |
