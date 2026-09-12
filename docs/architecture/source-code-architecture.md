# Flow Enterprise Platform Source Code Architecture

## Intent

Flow is a Laravel modular monorepo. It preserves a modular monolith today and creates explicit seams for independently deployable services tomorrow. The existing application under `app/Domains` remains supported during incremental migration; new platform-neutral capability is created under `packages`.

## Repository boundaries

| Area | Owns | May depend on |
| --- | --- | --- |
| `apps` | Application composition, route registration, deployment configuration | packages and modules through public contracts |
| `packages` | Reusable platform capabilities | platform-core and public package contracts |
| `modules` | Business bounded contexts | packages and published module contracts/events |
| `frontend` | Shared React UI and app screens | versioned API/SDK contracts only |
| `infrastructure` | Runtime and delivery configuration | no application domain logic |

Apps never become a dumping ground for business logic. A package never imports a vertical module. Modules never access another module's persistence directly.

## Package and module layout

Every package follows this layout:

```text
Application/     Commands, Queries, DTOs, UseCases, Handlers, Validators, Jobs, Listeners, Notifications
Domain/          Entities, Aggregates, ValueObjects, Services, Repositories, Specifications, Factories, Events, Exceptions, Policies
Infrastructure/  Eloquent, Redis, Storage, Mail, Queue, Search, AI providers, external APIs
Presentation/    Controllers, API, Requests, Resources, Middleware, React pages
Routes/ Config/ Database/ Resources/ Tests/ README.md
```

Modules have the same four layers plus routes, database, resources, tests, and documentation. Each production module has a README, API surface, events, permissions, seeders, documentation, changelog, and the complete test suite.

## Dependency direction

```text
Presentation -> Application -> Domain <- Infrastructure
                         ^                 |
                         +-- interfaces ---+
```

Controllers authorize and translate HTTP only. They invoke a use case or command handler; they never resolve or call a repository. Domain code declares repository interfaces. Infrastructure implements and binds those interfaces in the app composition root.

## Event and integration flow

```text
Controller -> Use case -> Domain event -> outbox -> subscribers
                                             |-> workflow / notifications
                                             |-> analytics / search / AI
                                             +-> integrations
```

Events are immutable, versioned, tenant-aware facts. Delivery is asynchronous where possible and must be idempotent. Cross-context side effects subscribe to events or use a documented public query contract.

## Tenant and security rules

Tenant context is resolved by trusted server-side middleware before a command, job, listener, or query runs. Tenant-owned aggregate tables use UUIDs, `tenant_id`, tenant-scoped indexes, actor attribution, soft deletion, and row versioning unless a documented exception exists. Authorization is evaluated in the Presentation/Application boundary and sensitive actions are audited.

## Microservice migration rule

A context is extraction-ready when it exposes versioned API/event contracts, keeps persistence private, avoids synchronous cross-context writes, and has an outbox-backed event stream. Extraction moves an adapter and deployment unit, not business behavior.

## Verification

Architecture tests verify the registered package structure and the controller repository boundary for new package code. Legacy `app/Domains` remains outside that enforcement until its controllers are migrated to application use cases; the current Metadata controller is a known baseline exception. CI runs linting, static analysis, architecture tests, unit and feature tests, security checks, image build, and deploy stages in that order. Performance/load/UI/E2E suites run on their designated environments.
