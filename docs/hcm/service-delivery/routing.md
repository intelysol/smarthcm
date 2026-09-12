# Intelligent Routing & Specialist Queues

## Routing Engine Architecture
The `ServiceRoutingService` routes incoming requests dynamically across tiered functional queues and specialist teams:

```mermaid
graph TD
    Inbound[Incoming Request / Ingestion] --> Analyze{Attribute Analysis}
    Analyze -- Category: Payroll/Comp --> PayQueue[PAYROLL_SUPPORT_QUEUE]
    Analyze -- Category: Benefits --> BenQueue[BENEFITS_SUPPORT_QUEUE]
    Analyze -- Confidentiality: Restricted / ER --> ERQueue[ER_SPECIALIST_QUEUE]
    Analyze -- Standard Inquiry --> Tier1Queue[HR_SHARED_SERVICES_QUEUE]

    Tier1Queue --> MemberAssign{Team Assignment Logic}
    PayQueue --> MemberAssign
    BenQueue --> MemberAssign

    MemberAssign -- Workload Based --> AgentLeast[Agent with lowest active ticket count]
    MemberAssign -- Round Robin --> AgentNext[Next available agent in rotation]
```

---

## Assignment Methods
* **Round Robin**: Distributes sequentially among active, available queue members.
* **Workload-Based**: Selects agents with the lowest count of active (`in_progress`, `assigned`) tickets below their max capacity threshold.
* **Skill / Specialist-Based**: Routes to designated Tier 2 teams (`HrServiceTeam`) equipped with domain certifications.
