# EPIC 2.61 — Database Architecture
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

---

## 1. Database Entity-Relationship Overview

Epic 2.61 leverages the existing relational schema created by `2026_09_09_000001_create_enterprise_hr_service_delivery_tables.php` and `2026_09_04_000001_create_employee_relations_tables.php`.

```text
               +---------------------------+
               |   hr_service_categories   |
               +-------------+-------------+
                             | 1:N
               +-------------v-------------+
               |   hr_service_definitions  +----------------------+
               +-------------+-------------+                      | 1:N
                             | 1:N                                |
               +-------------v-------------+            +---------v------------+
               |    hr_service_versions    |            | hr_service_templates |
               +-------------+-------------+            +---------+------------+
                             | 1:1                                | 1:N
               +-------------v-------------+            +---------v---------------------+
               | hr_service_form_definitions|           | hr_service_generated_documents|
               +---------------------------+            +-------------------------------+

               +---------------------------+
               |     hr_service_queues     +---------------+
               +-------------+-------------+               |
                             | 1:N                         |
               +-------------v-------------+               |
               |  hr_service_queue_members |               |
               +---------------------------+               |
                                                           |
+-------------------+                          +-----------v-----------+
|     employees     +------------------------->+  hr_service_requests  |
+-------------------+  1:N                     +-----------+-----------+
                                                           |
          +-----------------------+------------------------+------------------------+
          | 1:N                   | 1:N                    | 1:N                    | 1:1
+---------v-----------+ +---------v-----------+  +---------v-----------+  +---------v-----------+
|hr_service_request_  | |hr_service_request_  |  |hr_service_request_  |  |hr_service_sla_      |
|fields               | |comments             |  |assignments          |  |instances            |
+---------------------+ +---------------------+  +---------------------+  +---------+-----------+
                                                                                    | 1:N
                                                                          +---------v-----------+
                                                                          |hr_service_sla_events|
                                                                          +---------------------+
```

---

## 2. Table Schemas & Key Attributes

1. **`hr_service_requests`**:
   - `id`: UUID primary key
   - `tenant_id`: Tenant UUID
   - `employee_id`: Authoritative employee reference
   - `hr_service_definition_id`: Service reference
   - `request_number`: Unique sequential human-readable identifier (e.g. `HR-REQ-2026-ABC123`)
   - `subject`, `description`, `priority`: Priority (`low`, `normal`, `high`, `urgent`, `critical`)
   - `status`: Status (`draft`, `submitted`, `received`, `under_review`, `assigned`, `waiting_for_employee`, `waiting_for_approval`, `in_progress`, `resolved`, `rejected`, `cancelled`, `closed`, `reopened`)
   - `confidentiality_level`: Confidentiality (`normal`, `confidential`, `restricted`)
   - `assigned_queue_id`, `assigned_user_id`: Queue & agent ownership
   - `sla_instance_id`, `due_at`, `first_response_at`, `resolved_at`, `closed_at`: SLA tracking
   - `employee_relation_case_id`: Optional link when escalated to formal confidential ER Case
   - `form_data`: JSON payload containing dynamic form responses

2. **`hr_service_request_comments`**:
   - `comment_type`: `'public'` (visible to employee in ESS) vs `'internal'` (strictly internal for HR team members)
   - `user_id`, `employee_id`, `message`, `attachments`

3. **`hr_service_sla_instances` & `hr_service_escalations`**:
   - `response_due_at`, `responded_at`, `resolution_due_at`, `resolved_at`
   - `status`: `'running'`, `'paused'`, `'warning'`, `'breached'`, `'met'`
   - `total_paused_minutes`: Accurate accumulation of paused time (e.g. while waiting for employee action)
   - `escalation_level`: Tier 1 (warning $\ge 80\%$) vs Tier 2 (breached $\ge 100\%$)

4. **`hr_service_feedback`**:
   - `rating`: 1 to 5 numeric stars
   - `satisfaction_level`: `'satisfied'`, `'neutral'`, `'dissatisfied'`
   - `timeliness_rating`, `knowledge_rating`, `helpfulness_rating`: Multi-dimensional metrics
