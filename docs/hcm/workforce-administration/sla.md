# Service Level Management (SLA) & Monitoring

## 1. Overview
Service Level Management monitors operational queues, exceptions, and task items to ensure HR service requests and issues are triaged and resolved within defined thresholds.

## 2. SLA Policies & Targets
- Configurable per entity type (`queue_item`, `exception`, `task`, `personnel_action`) and priority:
  - **Critical**: Response $\le 2\text{ hours}$, Resolution $\le 4\text{ hours}$
  - **High**: Response $\le 4\text{ hours}$, Resolution $\le 24\text{ hours}$ (1 business day)
  - **Medium**: Response $\le 12\text{ hours}$, Resolution $\le 72\text{ hours}$ (3 business days)
  - **Low**: Response $\le 24\text{ hours}$, Resolution $\le 120\text{ hours}$ (5 business days)

## 3. SLA Tracking & Breach Detection
- `OpsSlaInstance` records `started_at`, `response_due_at`, `first_response_at`, `resolution_due_at`, and `resolved_at`.
- The scheduled `CheckSlaBreachesJob` automatically flags overdue instances as `is_breached = true` and dispatches `HrSlaBreached` domain events for automated escalation notifications.
