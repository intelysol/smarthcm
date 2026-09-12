# Enterprise Event Architecture

The Events domain provides a durable envelope and append-only event store for
domain, integration, system, AI, and security events. Every event carries a
stable UUID, type/version, aggregate, tenant, correlation/causation chain,
actor, source module, environment, payload, tags, and occurrence timestamp.

`EventBus` is the publisher boundary; consumers can project deliveries into
Workflow, Communication, Analytics, AI, Search, Integration, Automation, and
Operations without coupling to source tables. Replays create a new event with
the original event as causation, preserving the immutable history.

APIs:

- `GET /api/v1/events`
- `POST /api/v1/events`
- `POST /api/v1/events/{event}/replay`

The `event_catalog` and `event_deliveries` tables provide schema compatibility,
consumer health, retry, dead-letter, and monitoring extension points.
