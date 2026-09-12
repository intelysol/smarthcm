# Automation Platform technical design

The `Automation` bounded context owns versioned low-code definitions,
execution traces and node-level results. Definitions are JSON graphs with
explicit node ids and typed actions; the runtime never evaluates arbitrary
PHP, SQL or user-supplied code. Side effects are represented as action intents
for owning domains and integrations to consume.

Executions are tenant-scoped, durable and auditable. Each node writes a step
record, preserving inputs, outputs, status and timing for dry runs, monitoring,
retry and human-intervention tooling. Only published automations execute;
draft definitions may be simulated through the same engine.
