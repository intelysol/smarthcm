# EPIC 2.61 — API Specification
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

All endpoints require authentication and resolve the tenant via `X-Tenant-ID` header or authenticated user profile.

---

## 1. HR Service Delivery Command Center & Analytics

### `GET /api/hr-services/dashboard`
Returns high-level operational command center metrics.
- **Response `200 OK`**:
```json
{
  "summary": {
    "open_cases": 42,
    "new_today": 8,
    "overdue": 3,
    "due_soon": 5,
    "unassigned": 2,
    "escalated": 1,
    "awaiting_employee": 6,
    "awaiting_approval": 4,
    "resolved_today": 12,
    "average_resolution_days": 1.4,
    "sla_compliance_rate": 95.2,
    "csat_average": 4.8
  },
  "queue_health": [
    {
      "id": "uuid",
      "name": "Payroll Support",
      "code": "PAYROLL",
      "active_cases": 14,
      "member_count": 3,
      "capacity_percent": 93.3,
      "is_over_capacity": false
    }
  ],
  "sla_risk_cases": [],
  "deflection_summary": {
    "deflection_rate": 81.5,
    "kb_searches": 340,
    "kb_helpful_votes": 88
  }
}
```

---

## 2. Case Management & Inbox

### `GET /api/hr-services/cases`
Query filtered case inbox.
- **Query Parameters**:
  - `status`: Filter by status (`new`, `assigned`, `in_progress`, `waiting_for_employee`, `waiting_for_approval`, `resolved`, `closed`, `cancelled`, `open`)
  - `priority`: `low`, `normal`, `high`, `urgent`, `critical`
  - `queue_id`: Queue UUID
  - `assigned_to`: User ID or `'unassigned'`
  - `employee_id`: Target employee UUID
  - `search`: Keyword string matching subject or request number
- **Response `200 OK`**:
```json
{
  "data": [
    {
      "id": "uuid",
      "case_number": "HR-REQ-2026-AB1234",
      "subject": "Employment Certificate Request",
      "service_name": "Employment Certificate",
      "employee": {
        "id": "uuid",
        "name": "Zain Ahmed",
        "employee_code": "EMP-001"
      },
      "priority": "HIGH",
      "status": "IN_PROGRESS",
      "queue_name": "General HR",
      "assigned_agent": "Fatima HR",
      "due_at": "2026-09-14T10:00:00Z",
      "sla_status": "running",
      "created_at": "2026-09-12T09:00:00Z"
    }
  ],
  "total": 42
}
```

### `GET /api/hr-services/cases/{id}`
Returns complete case details, field values, document attachments, chronological timeline, and public/internal messages.

### `POST /api/hr-services/cases/{id}/assign`
Assigns a case to a specific queue or agent.
- **Payload**:
```json
{
  "queue_id": "uuid",
  "user_id": 5,
  "reason": "Specialist reassignment"
}
```

### `POST /api/hr-services/cases/{id}/resolve`
Marks the case as resolved.
- **Payload**:
```json
{
  "resolution_notes": "Certificate generated and sent to employee."
}
```

### `POST /api/hr-services/cases/{id}/close`
Formally closes the case.

### `POST /api/hr-services/cases/{id}/comments`
Add message or internal note.
- **Payload**:
```json
{
  "message": "We require a copy of your updated passport.",
  "comment_type": "public" // or "internal"
}
```

### `GET /api/hr-services/cases/{id}/ai-summary`
Generates a structured, grounded case summary for authorized HR agents.

---

## 3. Service Catalog & Employee Submission

### `GET /api/hr-services/catalog`
Returns available services grouped by category with dynamic form schemas and SLA estimates.

### `POST /api/hr-services/requests`
Submits a service request, launching automatic queue routing, dynamic field creation, and SLA clock initialization.

### `POST /api/hr-services/cases/{id}/feedback`
Submits employee CSAT feedback on a resolved case.
- **Payload**:
```json
{
  "rating": 5,
  "comments": "Very quick resolution, thank you!",
  "timeliness_rating": 5,
  "knowledge_rating": 5,
  "helpfulness_rating": 5
}
```
