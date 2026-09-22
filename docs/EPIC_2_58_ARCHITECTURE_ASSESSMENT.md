# EPIC 2.58 — Architecture Assessment: HCM Productization, Tenant Administration, Configuration & Enterprise Control Center

## 1. Executive Summary
Epic 2.58 productizes the entire HCM suite by creating the **Enterprise Control Center** (`/admin`) and tenant administration layer.
It transitions the platform from a set of transactional modules into a fully configurable, multi-tenant enterprise software product capable of supporting complex enterprise organizations, localization (Pakistan & International), hierarchical configuration, feature dependency enforcement, setup health scoring, and administrative delegation.

---

## 2. Core Architectural Principles
1. **Configuration != Custom Development**:
   - Customer behavior is driven by metadata, settings, feature flags, role templates, and policies.
   - Zero hard-coded customer logic or branching.
2. **Hierarchical Scope Inheritance**:
   - Scope hierarchy: `Platform` $\rightarrow$ `Tenant` $\rightarrow$ `Legal Entity` $\rightarrow$ `Business Unit` $\rightarrow$ `Department` $\rightarrow$ `Location` $\rightarrow$ `Employee Group`.
   - Higher priority scopes override lower priority defaults with deterministic fallback.
3. **Versioned Immutability & Rollback**:
   - Every published change creates an immutable version record (`HcmTenantConfigVersion`).
   - Rollback is non-destructive, creating a new forward version that restores previous values.
4. **Governed Feature Dependencies**:
   - Module activation validates predecessor requirements (e.g. `PAYROLL` requires `CORE_HR` and `ORG_DESIGN`; `AI_CONCIERGE` requires `AI_PLATFORM` and `RESPONSIBLE_AI`).
   - Missing dependencies block activation with actionable warnings.
5. **Localization Foundations**:
   - Decoupled regional engines supporting Pakistan (`PKR`, `CNIC` 13-digit mask, July fiscal year start, statutory rules) and international profiles (`UAE`, `Saudi Arabia`, `UK`, `USA`).
6. **Unified Enterprise Control Center UI**:
   - Unified administrative cockpit consolidating workforce KPIs, setup health, module toggles, branding, localization, integrations, and system health diagnostics.

---

## 3. Database Schema
- `hcm_tenant_onboarding_wizards`: Resumable 5-step onboarding state machine (Company, Org, Workforce, Security, Modules).
- `hcm_tenant_configurations`: Scoped configuration settings across 14 enterprise categories.
- `hcm_tenant_config_versions`: Historical audit versions tracking before/after values and rollback lineages.
- `hcm_tenant_feature_flags`: Feature toggles with dependency trees and rollout scopes.
- `hcm_tenant_brandings`: Isolated tenant branding (logos, color palettes, portal titles).
- `hcm_tenant_localizations`: Localization profiles (currency, timezone, tax IDs, national masks).
- `hcm_tenant_role_templates`: System and custom role templates.
- `hcm_tenant_delegations`: Time-bounded administrative delegation with automated expiration.
- `hcm_tenant_setup_health`: Multi-factor setup health score (0–100%) across 9 functional dimensions.
- `hcm_tenant_data_transfers`: Governed data imports and exports with row-level validation and error reporting.
