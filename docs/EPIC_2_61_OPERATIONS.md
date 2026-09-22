# EPIC 2.61 — Operations & System Maintenance
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

---

## 1. Background Jobs & Queues

Epic 2.61 leverages queued background jobs for heavy or asynchronous operations:

1. **`EvaluateRequestSlaJob`**:
   - Evaluates active SLA instances, computes consumed time against resolution/response deadlines, transitions status to `warning` or `breached`, and records SLA events.
2. **`ProcessRequestEscalationJob`**:
   - Escalates requests exceeding SLA thresholds to queue supervisors or leads, updating priority where necessary.
3. **`AutoFulfillServiceRequestJob`**:
   - For services supporting automated template rendering (e.g. standard employment verification letters), renders the template body, generates the certificate record, and auto-resolves the ticket.
4. **`DetectDuplicateServiceRequestsJob`**:
   - Compares incoming tickets against open tickets for the same employee to advise agents on potential duplicate submissions.

---

## 2. Scheduled Cron Tasks

In enterprise production deployments, the Laravel scheduler runs:

| Frequency | Command | Description |
|---|---|---|
| **Every 15 minutes** | `php artisan hcm:service-sla-evaluate` | Evaluates SLA deadlines, logs warnings at $\ge 80\%$, and escalates breached tickets. |
| **Hourly** | Internal Queue Metric Refresh | Recomputes active queue member ticket counts and capacity ratios. |
| **Daily** | CSAT & Deflection Aggregation | Updates daily rolling CSAT benchmarks and deflection percentages. |

---

## 3. Monitoring & Observability

- **SLA Breach Monitoring**: Real-time logging of `ServiceRequestSlaBreached` events to observability metrics.
- **Audit Logging**: All case assignment changes, status movements, comment additions, and document generation steps are immutably logged into `hr_service_request_status_history` and `hr_service_request_assignments`.
- **Capacity Alerts**: When a queue's active tickets divided by active members exceeds the configured threshold (e.g. $> 100\%$), an operational alert is published to the Command Center dashboard.
