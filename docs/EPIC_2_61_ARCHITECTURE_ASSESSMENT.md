# EPIC 2.61 — Architecture Assessment
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

---

## 1. Existing System Assessment

SmartHCM already possesses extensive foundational capabilities that directly map to the requirements of Epic 2.61:

1. **Service Requests & Dynamic Catalog** (`App\Domains\SelfService\`):
   - Database tables: `hr_service_categories`, `hr_service_definitions`, `hr_service_versions`, `hr_service_form_definitions`, `hr_service_requests`, `hr_service_request_fields`, `hr_service_request_comments`, `hr_service_request_documents`, `hr_service_request_links`.
   - Models: Fully structured Eloquent models with UUID primary keys and soft deletes.
2. **Queue Management & Routing Engine**:
   - Tables: `hr_service_queues`, `hr_service_queue_members`, `hr_service_assignment_rules`.
   - Engine: `RequestAssignmentService` evaluates rules by category, service, department, branch, or round-robin workload distribution.
3. **SLA Management & Escalation Engine**:
   - Tables: `hr_service_sla_policies`, `hr_service_sla_instances`, `hr_service_sla_events`, `hr_service_escalations`.
   - Engine: `RequestSlaService` and `RequestEscalationService` evaluate warning ($\ge 80\%$) and breach ($\ge 100\%$) conditions.
4. **Knowledge Base & Deflection Engine**:
   - Tables: `hr_knowledge_categories`, `hr_knowledge_articles`, `hr_knowledge_article_versions`, `hr_knowledge_feedback`.
   - Engine: `KnowledgeBaseService` and `UnifiedServiceSearchAndAiService` index articles, measure helpfulness, and suggest deflection before ticket creation.
5. **Employee Relations & Sensitive Case Engine** (`App\Domains\EmployeeRelations\`):
   - Tables: `employee_relation_cases`, `employee_relation_case_types`, `employee_relation_case_assignments`, `employee_relation_case_notes`.
   - Mechanism: `ServiceRequestService::convertToHrCase` smoothly promotes routine tickets to formal confidential ER cases with isolated custody.
6. **CSAT & Feedback Engine**:
   - Tables: `hr_service_feedback`.
   - Engine: `ServiceFeedbackService` tracks 1–5 ratings, comments, and multi-dimensional scores (timeliness, knowledge, helpfulness).

---

## 2. Identified Capabilities to Introduce in Epic 2.61

While the foundational models exist, the operational **HR Command Center & Orchestration Layer** requires:
1. **Consolidated Operational Metrics Service** (`HrServiceDeliveryCommandCenterService`):
   - Unified operational aggregation: Open cases, new today, overdue, due soon, unassigned, escalated, awaiting employee/approval, resolved today, average resolution time, SLA compliance rate, queue capacity & overload warnings.
2. **Advanced Case Orchestrator** (`HrCaseOrchestrationService`):
   - Multi-dimensional case inbox querying with server-side tenant isolation.
   - Comprehensive chronological event timeline (creation, assignment changes, public vs internal comments, document attachments, SLA milestones, approval results).
   - Grounded AI case summarization (Issue, Timeline, Employee Request, Actions Taken, Missing Info, Next Step, Sources).
3. **Dedicated Endpoints (`/api/hr-services/*`) & Web Portal (`/portal/hr-services/*`)**:
   - Uniform REST API and Tailwind-powered Command Center UI integrating with ESS and MSS.
