# Architecture & Control Plane

## 1. High-Level Architecture
HCM Workforce Administration sits above individual HCM domains as an operational and governance control plane.

```
                         HCM OPERATIONS
                              │
          ┌───────────────────┼───────────────────┐
          │                   │                   │
          ▼                   ▼                   ▼
    HR Operations        Governance          Monitoring
          │                   │                   │
          └───────────────────┼───────────────────┘
                              │
       ┌──────────────┬───────┼────────┬──────────────┐
       ▼              ▼       ▼        ▼              ▼
     Core HR      Lifecycle  Payroll  Benefits     Compliance
       │              │       │        │              │
       ▼              ▼       ▼        ▼              ▼
 Performance     Expenses  Documents  Learning     Workforce
 Compensation     Safety   Recruitment  Mobility      Planning
```

## 2. Layering & Responsibility Separation
- **Presentation Layer**: Blade & React 19 / Inertia.js responsive dashboards with TanStack table filtering, status badges, and interactive wizards.
- **Application Layer**: DTO-driven controllers, query orchestrators, and validation pipelines.
- **Domain Services Layer**: Focused domain services (`OperationsQueueService`, `HrExceptionService`, `BulkOperationService`, `CrossDomainReconciliationService`, `EffectiveDatedChangeMonitoringService`, etc.).
- **Event & Async Layer**: Transactional domain events dispatched to asynchronous queued workers for non-blocking execution.
- **Persistence Layer**: Multi-tenant relational schema supporting both MySQL and PostgreSQL with UUID primary keys and soft deletes.
