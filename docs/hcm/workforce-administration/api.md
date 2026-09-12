# REST API Specifications

All endpoints are prefixed with `/api/v1/hcm/workforce-admin` and require standard bearer authentication and tenant scoping.

## 1. Operational Cockpit & Dashboard
- `GET /dashboard`: Aggregate executive summary metrics across workforce, lifecycle, compliance, documents, payroll, benefits, and queues.

## 2. Operational Queues
- `GET /queues`: List operational queues with pending item counts.
- `POST /queues`: Create a new operational queue.
- `GET /queues/{queue}/items`: Paginated queue items.
- `POST /queue-items/{item}/assign`: Assign item to user or team.
- `POST /queue-items/{item}/complete`: Mark queue item completed.

## 3. Exceptions Workspace
- `GET /exceptions`: List operational exceptions with filters.
- `POST /exceptions`: Record a detected exception.
- `GET /exceptions/{exception}`: Detailed view with assignment history and audit events.
- `POST /exceptions/{exception}/assign`: Assign exception.
- `POST /exceptions/{exception}/resolve`: Resolve exception with notes.

## 4. Bulk Operations Engine
- `GET /bulk`: List bulk operations.
- `POST /bulk`: Create draft bulk operation.
- `GET /bulk/{bulkOperation}`: Operation details, validations, and item breakdown.
- `POST /bulk/{bulkOperation}/validate`: Run dry-run validation.
- `POST /bulk/{bulkOperation}/approve`: Approve bulk operation.
- `POST /bulk/{bulkOperation}/execute`: Idempotently execute bulk changes.

## 5. Governance & Monitoring
- `POST /data-quality/scan`: Trigger asynchronous data quality scan.
- `POST /reconciliation/{rule}/run`: Execute cross-domain reconciliation check.
- `POST /calendar/sync`: Synchronize calendar event milestones.
- `GET /configuration-health`: Run diagnostic configuration health checks.
- `GET /ai-insights`: Retrieve advisory-only AI operational insights.

## 6. Checklists & 360° Employee View
- `GET /checklists/templates`: List active checklist templates.
- `POST /checklists/templates`: Create checklist template.
- `GET /checklists/instances`: List instantiated checklists.
- `POST /checklists/templates/{template}/instantiate`: Instantiate checklist for employee.
- `POST /checklists/items/{item}/complete`: Complete a checklist task item.
- `GET /effective-dated-changes`: Get changes by horizon (`today`, `7d`, `30d`, `90d`, `backdated`).
- `POST /impact-analysis`: Analyze cross-domain downstream impact.
- `GET /employees/{employee}/operational-view`: Consolidated 360° employee operational view.
- `GET /integrations/health`: Aggregated integration gateway telemetry.
