# Flow HCM — Employee Profile, Employee Directory, Org Chart, People Search & Workforce Identity

## 1. Executive Summary
Epic 2.31 implements the unified **Workforce Identity, Employee Profile, Directory, People Search, and Organizational Hierarchy** experience across Flow HCM.

Core HR remains the authoritative system of record for all employee and organization structures. This module functions as a high-performance **Read, Experience, and Resilient Aggregation Layer**:

```text
                         CORE HR (Authoritative System of Record)
                           │
                           ▼
                    EMPLOYEE IDENTITY (Stable Internal Identifier)
                           │
             ┌─────────────┼─────────────┐
             ▼             ▼             ▼
        EMPLOYEE        EMPLOYMENT    ORGANIZATION
        PROFILE          DATA          STRUCTURE
             │             │             │
             └─────────────┼─────────────┘
                           ▼
                 HCM PROFILE EXPERIENCE
                           │
      ┌────────────┬───────┼────────┬─────────────┐
      ▼            ▼       ▼        ▼             ▼
   Payroll      Benefits Learning Performance Documents
      │            │       │        │             │
      └────────────┴───────┼────────┴─────────────┘
                           ▼
             RESILIENT PROFILE AGGREGATION
                           │
                           ▼
         DIRECTORY & INTERACTIVE ORG CHART
```

---

## 2. System of Record Boundaries

- **Core HR Master (`App\Domains\Employee\Models\Employee`, `Department`, `Designation`, `Branch`, `WorkLocation`)**:
  - The single authoritative source for employee master records, active employments, designations, organizational units, and managerial reporting relationships.
- **Employee Profile & Directory Layer (`App\Domains\EmployeeProfile`)**:
  - Read/aggregation layer that exposes unified profile views, directory card listings, people search autocomplete, and tree hierarchies.
  - Does NOT maintain a duplicate employee master.
  - Manages non-authoritative projections (`employee_org_read_models`) to accelerate queries across large enterprises (100,000+ employees).
- **Subsystem Integration Contracts**:
  - **Documents (`App\Domains\EmployeeDocuments`)**: Provides verified document counts and personnel file references.
  - **Lifecycle (`App\Domains\Lifecycle`)**: Feeds career milestones into the employee timeline.
  - **Learning & Certifications**: Provides certification achievements and skill inventories.
  - **Payroll & Compensation**: Exposes compensation snapshots only to authorized HR personnel.

---

## 3. Resilient Profile Aggregation & Partial Failure Tolerance

When loading the unified profile summary via `EmployeeProfileService::getProfileSummary`, each subsystem is queried inside isolated protective blocks:

```php
try {
    $docCount = EmployeeDocument::where('employee_id', $employee->id)->count();
    $summary['sections']['documents'] = [
        'status' => 'available',
        'data' => ['total_documents' => $docCount],
    ];
} catch (\Throwable $e) {
    Log::warning('Documents subsystem unavailable', ['error' => $e->getMessage()]);
    $summary['sections']['documents'] = [
        'status' => 'unavailable',
        'message' => 'Document summary temporarily unavailable',
    ];
}
```

If any subsystem (e.g. Payroll or Documents) is temporarily unreachable or throws an exception, the remaining sections of the profile render without failure, ensuring zero platform downtime for employee identity navigation.

---

## 4. Interactive Organization Chart & Lazy Tree Loading

- **Root Nodes**: Top-level executive leadership where `reporting_manager_id` is null or at the apex of the tenant's hierarchy.
- **Children Subtree**: Direct reports fetched on-demand using `GET /api/v1/hcm/org-chart/{managerId}/children?depth=1`.
- **Focused Subtree**: Upward reporting chain to executive leadership combined with immediate direct reports, enabling instant context switching.
- **Span of Control**: Real-time aggregation of active direct reports per manager node.

---

## 5. Employee Self-Service Profile Change Requests

- Employees can propose edits to permissible personal fields:
  - Mobile phone & alternate mobile
  - Personal email address
  - Present and permanent address, city, postal code
  - Emergency contact phone
  - Profile photo
- **Authoritative Employment Fields Guardrail**: Any attempt to modify salary, grade, department, designation, or reporting manager via self-service change requests is rejected at the API boundary, requiring formal HR Personnel Actions (Epic 2.28).
- **Workflow & Approval**:
  - Submitting creates `EmployeeProfileChangeRequest` (`PCR-YYYY-NNNNNN`).
  - HR review: Approval atomically updates the authoritative Core HR `Employee` record; rejection requires a mandatory explanation reason.

---

## 6. People Search & AI Advisory Assistant

- **Search Capabilities**:
  - Substring and prefix matching on employee full name, employee number, code, email, designation, and department.
  - Instant autocomplete endpoint (`/api/v1/hcm/people-search/autocomplete`).
- **AI Natural Language People Search**:
  - Parses freeform English prompts (e.g. "Software engineers in Karachi with PHP skills") into structured criteria (`['job_title' => 'Software Engineer', 'location' => 'Karachi', 'skill' => 'PHP']`).
  - All AI operations are advisory (`is_advisory => true`).
- **Safety Guardrails**:
  - AI is programmatically blocked from evaluating or participating in adverse employment decisions (termination, salary deductions, demotions).
  - All search queries respect tenant boundaries and field-level permissions.

---

## 7. Security, Privacy & RBAC

- **Tenant Isolation**: Cross-tenant profile and directory queries are strictly prohibited and throw an `AuthorizationException`.
- **Classification Levels**:
  - `PUBLIC_WORKFORCE`: Name, job title, department, work email, office extension.
  - `EMPLOYEE`: Personal email and mobile numbers (governed by employee preferences).
  - `MANAGER`: Direct reports, team structure, and operational status.
  - `HR / SENSITIVE`: Detailed employment timelines, document compliance, and personal history.
  - `HIGHLY_RESTRICTED`: Compensation structures and employee relations records (requires `employee_profile.view_sensitive`).
