# Enterprise Business Rules Engine

Rules are tenant-scoped, versioned configurations. Conditions are safe JSON
trees and actions are allow-listed typed intents; no tenant-provided expression
can execute application code.

## API

All routes are under `/api/v1/rules` and require `X-Tenant` authentication.
`rules.view` lists definitions, `rules.manage` creates/transitions them, and
`rules.execute` runs dry-run simulations.

| Route | Purpose |
| --- | --- |
| `GET/POST /` | Rule explorer and draft creation |
| `GET /{id}` | Rule editor definition |
| `POST /{id}/transition` | Lifecycle transition |
| `POST /{id}/test` | Dry-run with trace, output and performance metrics |

Only published rules within their effective period should be selected by event,
queue, API, workflow, scheduler, or batch binding consumers. Side-effecting
actions are returned as intents and must be handled by their owning domain.
