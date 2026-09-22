# End-to-End (E2E) Journey Automation & Verification

## 1. Scope of Critical User Journeys

Unit tests verify algorithms, and feature tests verify single requests. E2E journey tests verify continuous multi-step transactions across differing actors, permissions, and database entities to guarantee end-to-end platform coherence.

---

## 2. Certified Core User Journeys (`tests/E2E/CriticalHcmJourneysTest.php`)

### Journey 1: Employee Self-Service Profile & Workplace View
- **Flow**: Employee authenticates -> Queries `/api/me/profile` -> Validates personal information, department assignment, and work credentials.
- **Verification**: Verifies JSON structure contains `personal`, `employment`, and contact data matching the database state.

### Journey 2: Leave Request Submission & Manager Approval
- **Flow**:
  1. Employee applies for leave via `POST /api/me/leave/apply` with start date, end date, and leave type.
  2. Database registers record in `leave_applications` in `PENDING` status.
  3. Manager reviews and approves leave application via `POST /portal/leave/applications/{id}/approve`.
  4. Status transitions to `APPROVED` and employee leave balance deducts accordingly.

### Journey 3: Recruitment Requisition Lifecycle
- **Flow**:
  1. Hiring Manager drafts job requisition in `hcm_recruitment_requisitions` with job title, openings count, and salary band.
  2. HR Director approves and activates requisition (`status = 'OPEN'`).
  3. Public/internal job board receives published position.

### Journey 4: Attendance Clock Toggle
- **Flow**:
  1. Employee punches in via `POST /api/me/attendance/clock` (`action = 'toggle'`).
  2. System records `clock_in` timestamp and sets session status to `PRESENT`.
  3. Employee punches out via second toggle -> System calculates total worked hours.

### Journey 5: SaaS Commercial Subscription Lifecycle
- **Flow**:
  1. Tenant subscribes to Enterprise Plan (`billing_subscriptions`).
  2. System assigns seat quotas and active status.
  3. Tenant triggers plan upgrade -> Pricing adjustments and renewal periods update automatically.

### Journey 6: AI Concierge Policy Retrieval & Grounding
- **Flow**:
  1. Employee asks question regarding company policy via `POST /api/me/ai/chat`.
  2. AI Concierge performs grounded retrieval over company documents and returns authoritative citations.

### Journey 7: Multi-Tenant Tenant Administration
- **Flow**:
  1. Super Admin manages tenant lifecycle via `/portal/tenants`.
  2. System verifies tenant isolation, company linkages, and default administrator provisioning.
