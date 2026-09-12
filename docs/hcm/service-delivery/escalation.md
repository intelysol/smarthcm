# Escalation Framework & Background Monitoring

## Multi-Level Escalation Matrix
The background escalation scheduler (`php artisan hcm:shared-services-monitor` or queued `ProcessServiceSlaEscalationsJob`) scans active requests continuously:

```mermaid
graph TD
    Monitor[Shared Services Monitor / Scheduler] --> Scan{Check SLA Expiration}
    Scan -- Near Breach (75% Elapsed) --> Warning[Warning Notification to Assignee]
    Scan -- Response Breached --> Esc1[Level 1 Escalation: Notify Team Lead]
    Scan -- Resolution Breached (>4h overdue) --> Esc2[Level 2 Escalation: Reassign to Supervisor / Queue Manager]
    Scan -- Critical Breached (>24h overdue) --> Esc3[Level 3 Escalation: Executive HR Operations Alert]
```

---

## Escalation Actions & Audit Trails
When an escalation triggers:
1. `HrServiceRequest::escalation_level` is incremented.
2. `HrServiceRequestStatusHistory` logs the escalation event, trigger criteria, and automated actions taken.
3. Notifications are dispatched to queue leads and managers.
4. Auto-reassignment policies reallocate tickets if the current assignee is unresponsive.
