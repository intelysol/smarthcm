# EPIC 2.61 — Technical Implementation Guide
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

---

## 1. Domain Namespace & Services Architecture

All new Command Center and orchestration services reside under `App\Domains\ServiceDelivery\Services`:

```text
app/Domains/ServiceDelivery/
├── Http/
│   └── Controllers/
│       ├── HrServiceDeliveryApiController.php
│       └── HrServiceDeliveryWebController.php
├── Routes/
│   ├── api.php
│   └── web.php
└── Services/
    ├── HrServiceDeliveryCommandCenterService.php
    ├── HrCaseOrchestrationService.php
    └── HrServiceDeflectionService.php
```

---

## 2. Service Roles & Integration

1. **`HrServiceDeliveryCommandCenterService`**:
   - Integrates `HrServiceRequest`, `HrServiceQueue`, `HrServiceSlaInstance`, `HrKnowledgeArticle`, and `HrServiceFeedback`.
   - Produces executive and operational summaries: Open, New Today, Overdue, SLA compliance rate, Average resolution time, Queue capacity percentages, and CSAT scores.

2. **`HrCaseOrchestrationService`**:
   - Manages case lifecycle transitions: `ASSIGNED`, `IN_PROGRESS`, `WAITING_FOR_EMPLOYEE`, `WAITING_FOR_APPROVAL`, `RESOLVED`, `CLOSED`.
   - Gathers multi-domain chronological timeline items (Status changes, public/internal notes, assignment transfers, document uploads, and SLA milestones).
   - Powers grounded AI Case Summarization: Synthesizes structured insights with citations and advisory guardrails.

3. **`HrServiceDeflectionService`**:
   - Monitors search inquiries and knowledge article views relative to ticket creation to measure the enterprise deflection rate.
