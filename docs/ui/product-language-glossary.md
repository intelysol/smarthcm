# SmartHCM Enterprise Product Language Glossary

> **Standardized Enterprise UX Vocabulary (Epic 2.70)**  
> **Status:** Authoritative Standard  
> **Scope:** All Workspaces, Navigation, APIs, Models, Views, and Notifications

---

## 1. Core Principle

To deliver a cohesive, unified enterprise application experience, terminology across all 7 workspaces must remain strictly consistent. Technical jargon, redundant synonyms, and inconsistent naming conventions must not be presented to end-users.

---

## 2. Canonical Product Entities

| Canonical Term | Prohibited / Deprecated Synonyms | Definition & Context | Workspace Visibility |
| :--- | :--- | :--- | :--- |
| **Employee** | Worker, Staff, Personnel, Resource, Agent | An individual employed by the organization holding an active or historical employment record. | All Workspaces |
| **Manager** | Boss, Supervisor, Lead, Head | An employee with designated direct reports and approval authority over team workflows. | Manager, HR, Tenant Admin, Executive |
| **Organization** | Company, Enterprise, Corp, Firm, Account | The top-level corporate entity or tenant subscribed to the SmartHCM platform. | Tenant Admin, Platform Admin, Executive |
| **Department** | Division, Unit, Section, Branch | An organizational business unit grouping employees by business function. | Tenant Admin, HR, Manager, Executive |
| **Position** | Post, Slot, Opening, Vacancy | A specific budgeted job slot within a department with defined reporting hierarchy. | Tenant Admin, HR, Executive |
| **Job Role / Job Title** | Designation, Trade, Occupation | The standardized classification of responsibilities, salary grade, and competencies. | Tenant Admin, HR, Manager, Employee |
| **Workspace** | Portal, Hub, Area, Module, Section | A dedicated, role-aware application context tailored to a specific enterprise persona. | All Workspaces |
| **Request** | Ticket, Application, Submission, Form | An action initiated by an employee requiring multi-step review or operational processing. | Employee, Manager, HR |
| **Approval** | Sign-off, Clearance, Decision, OK | The formal evaluation and authorization/rejection of an employee request by a manager or administrator. | Manager, HR, Tenant Admin |
| **Direct Report** | Subordinate, Underling, Junior | An employee who reports directly to a specified manager within the organizational hierarchy. | Manager, HR |

---

## 3. Standardized Action Verbs

| Action | Canonical Verb | Deprecated Equivalents | Notes |
| :--- | :--- | :--- | :--- |
| **Initiate Request** | `Submit Request` | Send, Apply, File, Raise | Clear, predictable primary CTA |
| **Confirm Review** | `Approve` | Accept, OK, Grant, Validate | Standard workflow sign-off |
| **Decline Review** | `Reject` | Deny, Refuse, Cancel, Drop | Accompanied by required reason |
| **Discard Draft** | `Cancel` | Abort, Close, Quit | Return to previous state without changes |
| **Permanent Removal** | `Delete` | Remove, Purge, Erase, Kill | Always requires destructive confirm modal |
| **Access Revocation** | `Deactivate` | Disable, Block, Turn Off | Reversible administrative action |
| **Output Generation** | `Export` | Download, Extract, Dump | Standard CSV / Excel / PDF exports |

---

## 4. Status Terminology

| Status Token | Visual Indicator | Meaning |
| :--- | :--- | :--- |
| **Active** | Emerald / Green | Record is currently operational and in good standing. |
| **Inactive** | Slate / Gray | Record is paused, archived, or disabled. |
| **Pending** | Amber / Yellow | Awaiting review, approval, or background processing. |
| **Approved** | Emerald / Green | Formally authorized and scheduled for execution. |
| **Rejected** | Rose / Red | Declined with specified reason. |
| **Draft** | Slate / Muted | Work-in-progress, not yet submitted for processing. |
| **Processing** | Amber / Pulse | Background job or integration currently executing. |
| **Completed** | Emerald / Green | Process or journey finished successfully. |
| **Failed** | Rose / Red | Execution failed; requires user retry or administrator review. |
| **Cancelled** | Slate / Muted | Withdrawn by initiator before completion. |
| **Expired** | Slate / Warning | Timeframe lapsed without resolution. |

---

## 5. Workspace Domain Labels

1. **Platform / Super Admin Control Center**: `/platform/*`
2. **Tenant Administration Portal**: `/admin/*`
3. **HR Operations Workspace**: `/hr/*`
4. **Manager Workbench**: `/manager/*`
5. **Employee Self-Service**: `/employee/*` and `/portal/*`
6. **Executive Intelligence Workspace**: `/executive/*`
7. **Operational Command Center**: `/operations/*`
