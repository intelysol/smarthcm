# Flow Enterprise Platform Architecture Contract

## Purpose

Flow Enterprise Platform (FEP) is a multi-application enterprise platform. HCM is an application on the platform, not the platform's defining boundary. New capability must remain reusable by future CRM, ERP, finance, procurement, inventory, manufacturing, healthcare, education, government, logistics, facilities, and customer-built applications.

This document is the implementation contract for this repository. It translates the FEP master specification into decisions that can be applied incrementally while preserving the existing modular Laravel application. It is subordinate to the mandatory [Master Development Constitution](master-development-constitution.md), which takes precedence whenever the documents differ.

## Current Baseline

The repository currently uses Laravel 13 and PHP 8.3. The FEP target specifies Laravel 12 and PHP 8.4+. No framework downgrade or PHP-runtime upgrade is performed by this document. Compatibility must be maintained until a separately planned upgrade is approved and verified.

Existing HCM domains under `app/Domains` are retained as bounded contexts. New shared capability belongs in `app/Domains/Platform` or `app/Domains/Shared`; it must not be implemented in an HCM domain merely because HCM is the first consumer.

## Bounded Context Rules

Each domain owns its models, migrations, services, DTOs, actions, events, policies, requests, API resources, routes, tests, and documentation. A domain must not write directly to another domain's tables.

Cross-domain collaboration uses one of these mechanisms:

1. A public contract owned by the providing domain.
2. A domain event and listener.
3. A read model or explicitly documented query API.

Controllers only coordinate requests, authorization, and responses. Business behavior belongs in actions and services. Models express persistence and relationships only.

## Platform Capabilities

Platform-level services are reusable and application-neutral:

- Identity, authentication, RBAC/ABAC, tenant isolation, and audit.
- Metadata, workflow, rules, automation, notifications, storage, localization, search, feature flags, configuration, and marketplace.
- Analytics, AI context providers, API gateway, integration hub, and observability.

An HCM-specific policy, field name, or lifecycle must not be introduced into a platform service. Platform contracts accept neutral identifiers and typed payloads; applications translate their own concepts at their boundary.

## Data Contract

New tenant-owned aggregates use UUID primary keys and a tenant identifier, with tenant-scoped indexes and foreign keys. Where the aggregate has user-facing mutable state, include actor attribution (`created_by`, `updated_by`, and when applicable `deleted_by`), timestamps, a row version for optimistic locking, soft deletion, and audit metadata.

Tables that cannot conform immediately must document the exception and remediation plan in their domain documentation. Do not silently add cross-tenant queries or rely on a client-supplied tenant identifier without server-side authorization.

## Delivery Contract for New Domains

Every production domain increment includes:

- Migrations, models, factories, seeders, and tenant-scoped data access.
- Typed DTOs, actions/services, requests, policies, API resources, and versioned routes.
- Permissions, audit events, notifications, search and analytics events where applicable.
- Integration, workflow, automation, and AI extension points through platform contracts.
- Unit, feature, policy, and API tests; UI tests when a user interface is delivered.
- Domain, API, permission, event, and operational documentation.

API endpoints implement validated filtering, sorting, pagination, field selection, relationship includes, and standard error responses. They must remain versionable and rate-limitable.

## Security and Operations

All commands and jobs run within a resolved tenant context and enforce policies before changing data. Secrets use environment or managed-secret configuration, never application records or client payloads. Security-sensitive operations are auditable.

Long-running imports, integrations, analytics, and automation execute through queues with retry, idempotency, and observable failure handling. New domains must avoid N+1 access and publish useful domain/analytics events.

## Frontend Contract

When the React/Inertia frontend is introduced or extended, screens use reusable typed components with responsive layouts, dark-mode support, accessibility, keyboard navigation, loading, empty, and error states. Lists expose server-side search, filters, bulk actions, import/export, and appropriate pagination or infinite scrolling.

## Incremental Adoption Plan

1. Establish typed platform contracts for tenant context, auditing, event publication, permissions, and API errors.
2. Bring each new domain to the delivery contract above; do not introduce further migration-only modules.
3. Add compatibility tests and documentation before moving existing modules to the contracts.
4. Plan the PHP 8.4 and framework-version alignment as a dedicated, tested infrastructure change.

## Non-Negotiable Review Questions

Before merging a feature, verify:

- Is it application-neutral when placed in the platform?
- Does it preserve tenant isolation and policy enforcement?
- Does it avoid direct writes into another domain's tables?
- Are validation, authorization, auditing, and failure behavior tested?
- Can a future application consume it without HCM assumptions?
