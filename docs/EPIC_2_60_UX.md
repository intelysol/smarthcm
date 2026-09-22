# EPIC 2.60 — UX Design & Information Architecture
## HCM Employee & Manager Experience Layer

---

## 1. Design Principles
1. **One Coherent Workplace**: Not a loose aggregation of modular dashboards, but an integrated daily workspace where tasks, schedules, and requests flow seamlessly.
2. **Actionable Over Informational**: Priority tasks and quick actions are placed front and center to minimize clicks.
3. **Mobile-First Responsiveness**: All key daily tasks (clocking in/out, applying for leave, viewing payslips, approving team requests) are fully responsive across smartphones and tablets.
4. **Predictable Navigation**:
   ```text
   Home ─── My Work ─── My Requests ─── My Information ─── My Pay ─── My Growth ─── My Documents ─── HR Services ─── Directory
   ```

---

## 2. Layout Structure & Components

### 2.1 Header
- Brand logo & Portal title.
- Universal Search Bar (`Cmd+K` / `Ctrl+K`) for quick navigation across services, directory, and FAQs.
- "Ask HR" AI Concierge quick launch button.
- Notification Center popover with unread counter.
- User Profile avatar with role switch option (Employee $\leftrightarrow$ Manager).

### 2.2 Navigation Sidebar
- Collapsible sidebar with active tab indicator, icons, and pending badge counters (e.g. `3` pending tasks on My Work, `2` pending requests).
- Manager Workbench section automatically shown when the authenticated user manages direct reports.

### 2.3 Main Content Canvas
- **Greeting Banner**: Contextual greeting with time of day, current date, and high-level shift status.
- **Top Row Cards**:
  - Live Attendance Card (Current status: Clocked In / Out, timer, Clock In/Out action button).
  - Schedule Card (Today's shift: 09:00 - 18:00, location, break time).
  - Leave Balance Card (Annual leave remaining, quick "Apply Leave" link).
  - Payslip Card (Latest net pay summary, secure "View" link).
- **Activity Grid**:
  - Pending Tasks (Grouped by due date, urgency badge, direct action link).
  - Active Requests (Recent submissions, status pills, current step indicator).
- **Announcements Carousel**: Important company broadcasts with priority tag and "Acknowledge" button if required.
- **Embedded AI Concierge Drawer**: Slide-over panel enabling instant employee self-service queries with prompt suggestions.
