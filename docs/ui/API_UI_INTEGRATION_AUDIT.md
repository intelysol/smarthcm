# Frontend-to-Backend API Integration Audit

> Standardized Contracts and Payload Specifications for Epic 2.67

## 1. Standard API Envelope Specification

All API endpoints consumed by the UI adhere to the Enterprise JSON Contract:

### Success Envelope (Single Entity / Action)
```json
{
    "success": true,
    "data": {},
    "message": "Operation completed successfully."
}
```

### Success Envelope (Collections / Pagination)
```json
{
    "success": true,
    "data": [],
    "meta": {
        "current_page": 1,
        "per_page": 25,
        "total": 100
    }
}
```

### Error Envelope
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Please correct the highlighted fields.",
        "details": {}
    },
    "request_id": "req_xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
}
```

## 2. Portal & Self-Service API Contracts

| Frontend Action | Method | API Endpoint | Request Body | Response Data | Status |
| :--- | :---: | :--- | :--- | :--- | :--- |
| Attendance Clock Punch | POST | `/api/me/attendance/clock` | `{"type": "in"|"out", "notes": ""}` | `{"status": "PRESENT", "clock_in": "..."}` | CONNECTED |
| Apply Leave Request | POST | `/api/me/leave/apply` | `{"leave_type_id": 1, "start_date": "...", "end_date": "...", "reason": "..."}` | `{"request_id": "...", "status": "PENDING"}` | CONNECTED |
| Document Acknowledge | POST | `/api/me/documents/{id}/acknowledge` | `{}` | `{"acknowledged_at": "..."}` | CONNECTED |
| Manager Act on Approval | POST | `/api/manager/approvals/{type}/{id}/action` | `{"action": "approve"|"reject", "comment": "..."}` | `{"status": "APPROVED", "actioned_at": "..."}` | CONNECTED |
| AI Concierge Chat | POST | `/api/me/ai/chat` | `{"prompt": "..."}` | `{"response": "..."}` | CONNECTED |
| Global Search | GET | `/api/v1/hcm/search?q={query}` | Query parameters | `[{"title": "...", "url": "..."}]` | CONNECTED |
