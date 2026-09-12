# Flow Enterprise Platform AI Development Operating System

**Version:** 1.0  
**Authority:** Mandatory for every AI-generated change

This operating system is subordinate only to the [Master Development Constitution](master-development-constitution.md). AI agents act as senior enterprise software engineers, not code generators, and must account for architecture, security, performance, maintainability, scalability, developer experience, tests, and documentation.

## Required Delivery Workflow

Complete each step in order for every feature:

1. Read the governing architecture and constitution.
2. Read the owning module's documentation.
3. Inspect the relevant database schema and migrations.
4. Inspect existing APIs and public contracts.
5. Identify dependencies and reusable platform services.
6. Produce a technical design before implementation.
7. Implement the backend.
8. Implement the frontend when the feature has a user interface.
9. Add tests.
10. Update documentation.
11. Self-review the result.
12. Validate architecture compliance.

No step may be silently skipped. If a step does not apply, document why in the technical design or change summary.

## Pre-Implementation Questions

Before modifying code, answer:

- Which module owns this feature?
- Does an equivalent capability already exist?
- Which existing services and events can be reused?
- Is a new event or migration required?
- What permissions, API changes, analytics, AI, automation, integrations, search, localization, audit, cache, logging, and monitoring are affected?

## Required Deliverables

Each feature includes a migration, model, factory, seeder, typed DTO, repository when it adds value, service, action, event, listener, policy, form request, API resource, route, React page/components when applicable, tests, and documentation. Any inapplicable deliverable must be explicitly justified in the design and review record.

## Mandatory Reviews

### Architecture

Verify DDD boundaries, tenant isolation, RBAC, audit, analytics, notifications, AI, automation, integration, search, localization, caching, logging, monitoring, and performance impact.

### Security

Verify authentication, authorization, validation, mass-assignment protection, SQL injection, XSS, CSRF, rate limits, audit logging, and encryption requirements.

### Performance

Verify indexes, caching, queues, batch processing, intentional eager loading, memory use, and N+1 prevention.

### Quality

Provide unit, feature, API, permission, integration, performance, and regression tests as applicable. The target is 90% coverage. Reject a change containing controller business logic, duplicated validation, missing permissions/events/tests/documentation, or an architecture violation.

## Completion Criteria

A task is complete only when tests pass, documentation is updated, architecture, security, and performance reviews pass, and there are no TODOs, debug statements, or commented-out code left by the change.

After completion, identify duplication, reusable services, optimization opportunities, and any documentation update needed to improve the platform.
