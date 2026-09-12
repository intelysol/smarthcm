# Business Rules Engine Technical Design

## Scope and ownership

The `Rules` bounded context owns business-rule definitions, lifecycle, safe
evaluation, execution traces, simulations, monitoring queries and bindings. It
does not own subject data, workflow instances, notifications or integrations.
It emits typed rule events for their owning domains to consume.

## Evaluation model

Conditions use a JSON abstract syntax tree: nested `all`, `any`, and `not`
groups contain comparisons with a path, operator and literal/variable value.
The evaluator resolves dotted paths from an explicit execution context and
supports equality, ordering, ranges, containment, emptiness, list membership,
and safe arithmetic/string/date helper functions. It never evaluates PHP,
JavaScript, SQL, or untrusted callback code.

Actions use a typed allow-list: set/calculate field values, UI state changes,
required state changes, notification/workflow/integration/job/document intents,
related-record updates, exceptions and logs. Side-effecting actions produce
intents in the result. Owning services execute those intents through later
listeners; dry-runs never cause side effects.

## Lifecycle and versioning

Rules progress through draft, under-review, approved, published, deprecated and
archived states. Publishing creates a new immutable version using the same
business key; rollback publishes a copy of a selected historical version. Only
published, effective, tenant-scoped rules execute.

## Tenant isolation and observability

All Rules APIs execute under `TenantContext`. The tenant id is server-resolved,
and rule/subject execution never trusts a client tenant id. Every definition
change and execution writes Rule audit/execution records; `RuleExecuted` is the
extension point for analytics, automation, notification, AI and monitoring.
Rule selection uses tenant, trigger, lifecycle and effective-date indexes.

## Delivery limitation

The API provides the stable contract for React rule explorer/editor/tester and
monitoring pages. The local runtime still cannot install the required React and
Inertia dependencies, so no unbuildable frontend source is committed.
