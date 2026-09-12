# Platform Operations Center technical design

The `Operations` bounded context owns normalized operational metrics, alert
rules, active alerts, and incident lifecycles. Metrics are tenant-scoped (or
platform-wide when explicitly emitted with a null tenant), while all
administrator APIs resolve tenant identity server-side and require operations
permissions.

Alert evaluation is deterministic and supports threshold operators with
severity and channel metadata. Incidents retain timeline, assignee, root-cause,
and resolution state. Queue, scheduler, integration, analytics, and AI
adapters should publish metrics into this contract rather than coupling the
Operations Center to provider-specific tables.
