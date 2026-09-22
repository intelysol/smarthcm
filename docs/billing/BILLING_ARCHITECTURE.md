# Enterprise SaaS Billing Architecture & Commercial Platform Layer

## 1. Executive Summary

The Enterprise Commercial Platform layer establishes a centralized, multi-tenant billing, subscription, entitlement, and licensing engine residing in `packages/billing` and consumed across applications (`Enterprise HCM`, and future `CRM`, `ERP`, `Government`, and `Healthcare` systems).

```
                 ┌──────────────────────────────────────────────┐
                 │       Platform Commercial Layer              │
                 │          (packages/billing)                  │
                 ├──────────────────────┬───────────────────────┤
                 │ - Product Hierarchy  │ - Usage Metering      │
                 │ - Versionable Plans  │ - Invoices & Items    │
                 │ - Pricing & Proration│ - Payment Abstraction │
                 │ - Entitlement Engine │ - Credits & Discounts │
                 │ - Subscription Cycle │ - Reconciliation      │
                 └──────────┬───────────────────┬───────────────┘
                            │                   │
             ┌──────────────┴──────┐     ┌──────┴────────────────┐
             ▼                     ▼     ▼                       ▼
    ┌────────────────┐   ┌───────────────┐   ┌────────────────┐   ┌────────────────┐
    │ Enterprise HCM │   │  Future CRM   │   │  Future ERP    │   │ Future Gov/Edu │
    │ (Entitlement   │   │ (Entitlement  │   │ (Entitlement   │   │ (Entitlement   │
    │  Enforcement)  │   │  Enforcement) │   │  Enforcement)  │   │  Enforcement)  │
    └────────────────┘   └───────────────┘   └────────────────┘   └────────────────┘
```

---

## 2. Core Architectural Principles

1. **Platform Layer Ownership**:
   All commercial product catalogs, plans, pricing rules, invoices, payments, subscriptions, and entitlements belong to the platform layer. Individual applications do not construct disparate billing engines.
2. **Finance Domain Boundary**:
   Billing is distinct from Financial Accounting. Billing owns subscription lifecycles, invoices, and payments. The Finance domain remains authoritative for the General Ledger (GL), Chart of Accounts (COA), and accounting period closures.
3. **Strict Multi-Tenant Scoping**:
   All billing data structures (`billing_subscriptions`, `billing_invoices`, `billing_payments`, `billing_usage_events`, `billing_credits`) are strictly scoped to `tenant_id` (`CHAR(36)`), guaranteeing data isolation across tenants.
4. **Idempotency & Immutability**:
   Usage metering events are deduplicated via unique idempotency keys. Historical invoice line items and transaction records preserve fixed unit prices and cannot be mutated post-issuance.

---

## 3. Package & Domain Structure

```text
packages/billing/
├── Contracts/                 # Core interfaces for pricing, proration, entitlements, payments
├── Domain/
│   ├── Enums/                 # SubscriptionStatus, InvoiceStatus, PaymentStatus, PricingModel, etc.
│   └── Models/                # 22 Normalized Eloquent Models with strict tenant scoping
├── Infrastructure/            # BillingServiceProvider singleton bindings and event listeners
└── Services/                  # PricingEngine, ProrationCalculator, EntitlementResolver, InvoiceEngine, etc.
    └── Providers/             # MockPaymentProvider, StripeSandboxProvider
```

---

## 4. Key Subsystems

| Subsystem | Service Class | Description |
|---|---|---|
| **Product & Plans** | `ProductPlanService` | Manages products, versions, and versionable subscription plans |
| **Pricing Engine** | `PricingEngine` | Dynamic calculation for flat, per-seat, tiered, volume, and graduated models |
| **Proration** | `ProrationCalculator` | Deterministic, timezone-aware calculation for mid-cycle plan changes |
| **Entitlements** | `EntitlementResolver` | Decouples RBAC permissions from commercial features and capacity quotas |
| **Subscription Lifecycle** | `SubscriptionLifecycleService` | State machine governing trials, activations, renewals, grace periods, and suspensions |
| **Usage Metering** | `UsageMeteringService` | Idempotent event capture, pre-aggregated rollups, and threshold alerting |
| **Invoice Engine** | `InvoiceEngine` | Generates invoices with preserved line items, automated tax, and credit offsets |
| **Payments** | `PaymentManager` | Provider-neutral gateway router, settlements, refunds, and webhook reconciliation |
| **Credits & Discounts** | `CreditDiscountService` | Tenant wallet credits and promotional coupon redemption |
| **Reconciliation** | `BillingReconciliationService` | Discrepancy detector comparing invoices, payments, and gateway transactions |
| **Commercial Analytics** | `CommercialAnalyticsService` | Computes MRR, ARR, active subscriptions, churn, and ARPT |
