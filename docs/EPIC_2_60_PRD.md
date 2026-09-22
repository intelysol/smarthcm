# EPIC 2.60 — Product Requirements Document (PRD)
## HCM Employee & Manager Experience, Unified Self-Service Portal & Daily Workbench

---

## 1. Objective & Vision
Transform the enterprise HCM capabilities into a unified, coherent daily employee and manager experience. The portal serves as a unified orchestration layer over all transactional HCM domains (Core HR, Attendance, Leave, Payroll, Benefits, Documents, Learning, Performance, Expenses, Service Delivery, and AI Concierge), presenting them as an integrated, daily digital workplace rather than a fragmented set of isolated modules.

---

## 2. Core User Personas

### 2.1 The Enterprise Employee
- **Goal**: One unified home to view personal info, check schedules, punch attendance, view leave balances, apply for requests, view payslips, complete compliance tasks, learn, track goals, and interact with the HR AI Concierge.
- **Key Needs**: Fast loading, mobile responsiveness, clear task deadlines, transparent request approval timelines, secure access to private payroll and benefits data.

### 2.2 The People Manager
- **Goal**: A dedicated Daily Workbench to supervise direct reports, monitor daily attendance, approve multi-domain requests (leave, expenses, adjustments), monitor team capacity, track review/training compliance, and resolve team alerts.
- **Key Needs**: Centralized approval inbox with batch or inline decisions, team attendance heatmaps, real-time capacity and absence visibility, delegated actions.

---

## 3. Product Architecture & Navigation

The navigation is centered on employee-centric activities:
1. **Home**: Daily cockpit showing Greeting, Today's Schedule, Live Attendance Clock status, Leave Balance summary, Pending Tasks, Active Requests, Latest Payslip preview, Announcements, and Quick Actions.
2. **My Work**: Shift schedules, Attendance sessions, Clock in/out, Timesheet submission, Attendance exception corrections.
3. **My Requests**: Unified request center (Leave, Expenses, Service Requests, Profile Updates, Document Requests, Shift Swaps) with timeline steps and approver tracking.
4. **My Information**: Multi-tab Employee Profile (Personal, Employment, Organization, Bank, Emergency Contacts, Dependents, Skills) with controlled field change request workflows.
5. **My Pay**: Payslip history, secure viewing/downloading, earnings vs deductions breakdown, tax summaries.
6. **My Growth**: Goals, OKRs, Performance Reviews, Assigned Learning courses, Training paths, Certifications.
7. **My Documents**: Categorized document repository, document requests, mandatory policy review & digital acknowledgement.
8. **My HR Services**: Service Catalog, SLA-tracked ticket requests, multi-channel communication.
9. **My People**: Enterprise directory with role/department filters and interactive organizational chart.
10. **Ask HR (AI Concierge)**: Context-aware assistant grounded in authenticated employee identity.

For Managers:
11. **Manager Workbench**:
    - Team Overview & Today's Attendance Roster
    - Unified Approval Inbox (Leave, Expenses, Adjustments, Profile Changes, Services)
    - Team Alerts (Absence, Overdue tasks, Expiring certificates)
    - Team Capacity Summary & Utilization

---

## 4. Key Functional Requirements

### 4.1 Orchestration vs. Domain Authority
- The experience layer **consumes** domain capabilities; it **never duplicates** domain engines.
- Domain updates (e.g. Leave status changes, payroll records, attendance punches) are executed through existing domain services.

### 4.2 Security & Strict Isolation
- Employees can strictly view only their own records (payslips, reviews, documents, attendance).
- Managers can strictly supervise only direct and indirect reports within their organizational scope.
- AI Concierge context is strictly derived from the authenticated session (`Auth::user()->employee`), never from arbitrary client input parameters.

### 4.3 Task & Request Normalization
- Aggregates tasks across Onboarding, Documents (acknowledgements), Performance (reviews), Learning (courses), and Attendance (timesheets) into a unified contract.
- Normalizes all multi-domain requests into a standard timeline structure: `Submitted` $\rightarrow$ `Under Review / Approver` $\rightarrow$ `Validated` $\rightarrow$ `Completed`.

---

## 5. Non-Functional Requirements
- **Performance**: Consolidated dashboard payload with lightweight queries to avoid N+1 traversals.
- **Accessibility**: Semantic HTML, full keyboard navigation, screen-reader accessible ARIA roles, high-contrast states.
- **Responsiveness**: Mobile-first design for smartphones, tablets, and desktops.
- **Auditability**: All sensitive accesses (payslip downloads, profile changes, approvals) emit immutable audit logs.
