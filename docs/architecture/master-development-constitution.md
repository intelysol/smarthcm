# Flow Enterprise Platform Master Development Constitution

**Version:** 1.0  
**Authority:** Mandatory

## Authority and Vision

This is the highest authority for software development in Flow Enterprise Platform (FEP). Any implementation that conflicts with it is incorrect, even when it functions.

The mandatory execution procedure for AI-generated work is the [AI Development Operating System](ai-development-operating-system.md).

FEP is a metadata-driven, AI-ready enterprise application platform. It supports multiple applications from one codebase, including HCM, CRM, ERP, finance, procurement, manufacturing, healthcare, education, and government. HCM is the first application, not the architectural boundary.

## Core Principles

Every implementation is enterprise-grade, multi-tenant, cloud-native, secure by default, API-first, event-driven, metadata-driven, AI-ready, automation-ready, observable, extensible, and highly testable.

## Absolute Rules

The platform must never:

- Duplicate business logic or validation.
- Access another module's database tables directly.
- Bypass RBAC, tenant isolation, policies, or row-level authorization.
- Expose internal models through public APIs.
- Put business logic in controllers.
- Hard-code permissions, workflow behavior, business rules, notifications, or tenant-specific behavior.

## Module and Platform Boundaries

Each module owns its database, events, policies, services, permissions, APIs, tests, and documentation. Modules communicate only through events, contracts, or public services.

Reusable application-neutral capability belongs to the Platform, including identity, metadata, workflow, rules, notifications, AI, analytics, search, audit, storage, localization, automation, and integration.

## Mandatory Delivery Contents

Every AI-generated feature includes models, typed DTOs, actions, services, validation, policies, events, listeners, tests, API resources, and documentation. Partial implementations are not complete implementations.

Every user interface provides responsive design, accessibility, dark mode, loading and empty states, validation feedback, keyboard navigation, consistent components, search, filtering, export, and bulk actions where applicable.

Every API is versioned, uses standardized responses, respects tenant boundaries, records audit activity, and supports pagination, filtering, sorting, field selection, and OpenAPI documentation.

## Security and Performance

Features enforce RBAC and ABAC where appropriate, field and row-level security, encryption in transit and at rest, MFA compatibility, audit logging, and secret management. They intentionally avoid N+1 queries, cache expensive work, queue long-running work, and publish events instead of tightly coupling modules.

## Quality and Release Gates

Code is not complete until architecture, static analysis, tests, security, performance, and generated documentation have passed review.

Every release includes a migration and rollback strategy, upgrade notes, API compatibility review, security and performance review, plus user, administrator, and developer documentation.

## Long-Term Standard

Optimize for readability, maintainability, scalability, testability, reuse, and consistency—not fewer files. Design for a platform that remains maintainable for ten years, hundreds of developers, thousands of tenants, millions of employees, and hundreds of integrations.
