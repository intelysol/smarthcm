# EPIC 2.61 — UX & Design Specification
## HCM Employee Lifecycle Command Center, Case Orchestration & HR Service Delivery

---

## 1. User Experience Principles

1. **Information Density with High Scannability**:
   - HR operations desks require rapid situational awareness. The Command Center uses a dashboard layout with key metric cards, queue progress meters, and color-coded SLA alerts (Green = On Track, Amber = Warning $\ge 80\%$, Red = Breached).
2. **Visual Chronological Provenance**:
   - The Case Detail view features a clear, vertical timeline displaying every event (intake, queue routing, agent response, document upload, status changes, resolution).
3. **Seamless Employee Experience**:
   - In the Employee Portal (`/portal/services`), employees experience an intuitive Service Catalog with clear categories, estimated SLA fulfillment times, guided dynamic forms, and 1-click CSAT star ratings upon completion.

---

## 2. Screen Breakdown

### 2.1 HR Service Delivery Command Center (`/portal/hr-services`)
- **Metric Row**:
  - Open Cases, New Today, Overdue, SLA Compliance %, Avg Resolution Time, Escalations, CSAT Average.
- **Queue Health Grid**:
  - Cards for each queue (General HR, Payroll, Benefits, Attendance, Documents) showing active tickets, member count, and a capacity bar.
- **SLA At-Risk Table**:
  - Immediate view of cases approaching deadline with one-click re-assignment or escalation.

### 2.2 Case Inbox & Workbench (`/portal/hr-services/cases`)
- **Filters & Search Bar**: Filter by Queue, Priority, Status, Agent, or Keyword.
- **Data Table**: Columns for Case #, Subject & Service, Employee, Priority Pill, Status Badge, Queue, Assigned Agent, SLA Clock, Actions.

### 2.3 Case Detail & Workspace (`/portal/hr-services/cases/{id}`)
- **Header**: Case Number, Title, Status, Priority, Employee card, SLA countdown badge.
- **Main Column**:
  - Case Summary & Form Data
  - Dual-Tab Communication: "Public Messages (to Employee)" vs "Internal Notes (HR Only)"
  - File Attachment list with preview
  - Chronological Event Timeline
- **Sidebar**:
  - Assignment Box (Queue & Agent reassign buttons)
  - Status Action Buttons (`In Progress`, `Wait for Employee`, `Resolve`, `Close`)
  - AI Assistant Drawer: Grounded Case Summary & Recommended Knowledge Articles
