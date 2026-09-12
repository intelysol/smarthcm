# EPIC 2.55 — Architecture Assessment & Implementation Blueprint
## HCM Employee AI Concierge, Employee Self-Service Copilot, Personalized HR Assistance & AI-Powered Employee Experience

**Domain Namespace**: `App\Domains\EmployeeAi`  
**Database Tables Prefix**: `hcm_ai_concierge_*`  
**Role**: Employee-Facing AI Assistant, Conversational Copilot & Action Preparation Layer

---

### 1. Architectural Position & Extension Strategy
Epic 2.55 serves as the **Employee AI Concierge** layer. It consumes the underlying enterprise AI platform and authoritative HCM domains (Leave, Attendance, Scheduling, Payroll, Benefits, Expenses, Documents, Learning, Performance, HR Service Delivery).
- **No Duplicate AI Engine**: Reuses the core LLM orchestration, semantic querying, RAG, and audit logging.
- **No Duplicate Workflows**: Reuses existing domain workflows (`App\Domains\Workflow`, `App\Domains\Leave`, etc.) for actual state mutations.
- **Non-Bypassable Security**: Context resolution is grounded in the authenticated user (`$user->employee_id`). Client-provided employee IDs are strictly prohibited to prevent IDOR attacks.
- **Action Proposal Model**: `AI Prepares` -> `User Confirms` -> `Workflow Validates & Executes`. Never direct raw database writes.

---

### 2. Core Functional Pillars

#### 2.1 Authenticated Employee Context & "My Data" Security
- Automatically resolves:
  - Current Employee ID, Department, Position, Manager, Location
  - Leave balances & upcoming holiday schedule
  - Current month attendance summary & exceptions
  - Shift rosters & schedule coverage
  - Recent payslips & itemized deductions (where permitted)
  - Enrolled benefits & dependents
  - Pending learning courses & certifications
  - Performance goals & upcoming reviews
  - Open HR service requests & status

#### 2.2 Grounded Policy Assistant (RAG) with Source Priority
- Priority Order:
  1. Tenant HR Policy & Employee Handbook
  2. Operating HR Procedures & Guidelines
  3. General Platform Guidance
- Provides exact citations (`source_title`, `section`, `version`). Never invents policy.

#### 2.3 Self-Service Action Preparation
- Proposes actions:
  - `SUBMIT_LEAVE_REQUEST`
  - `SUBMIT_ATTENDANCE_CORRECTION`
  - `CREATE_HR_REQUEST`
  - `SUBMIT_EXPENSE`
  - `REQUEST_DOCUMENT`
- Provides interactive preview before submission. Requires explicit user confirmation.

#### 2.4 Manager & HR Personas
- **Manager Mode**: Scoped strictly to direct/indirect reports (`who is absent today`, `team schedule coverage`, `requests awaiting my approval`).
- **HR Mode**: Policy explanation, request triage, organizational analytics summaries with role-based restrictions.

---

### 3. Database Schema Overview (`hcm_ai_concierge_*`)

1. `hcm_ai_concierge_sessions`: Chat session metadata, tenant_id, user_id, persona, status.
2. `hcm_ai_concierge_messages`: Chronological conversation exchanges, role (`user`, `assistant`, `system`, `tool`), citations, action payload.
3. `hcm_ai_concierge_actions`: Prepared action proposals (`LEAVE_REQUEST`, `ATTENDANCE_CORRECTION`, `HR_REQUEST`), action payload, confirmation status (`PROPOSED`, `CONFIRMED`, `CANCELLED`, `SUBMITTED`).
4. `hcm_ai_concierge_suggestions`: Proactive lifecycle and task recommendations for the employee.
5. `hcm_ai_concierge_feedback`: Thumbs up/down, user ratings, and feedback notes.

---

### 4. Implementation Phasing
- **Phase 1**: Architecture Assessment Document (Completed)
- **Phase 2**: Schema Migration (`hcm_ai_concierge_*`)
- **Phase 3**: Enums, Value Objects & DTOs
- **Phase 4**: Eloquent Models & Service Contracts
- **Phase 5**: Core Concierge Services (Context, Policy, Actions, Suggestions, Manager Assistant)
- **Phase 6**: API Controllers & REST Endpoints
- **Phase 7**: Mobile-Ready Responsive Blade UI (`/me/ai`)
- **Phase 8**: Feature & Security Test Suite
- **Phase 9**: Documentation & Walkthrough
