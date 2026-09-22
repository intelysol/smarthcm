# Enterprise UI & Route Inventory

## 1. Route Registry by Workspace

### A. Platform Control Center
- `GET /platform` (`platform.index`): Redirects to Platform Control Center
- `GET /platform/control-center` (`platform.control-center`): Master control plane
- `GET /platform/tenants` (`platform.tenants`): Multi-tenant management
- `GET /platform/billing` (`platform.billing`): SaaS billing & subscriptions
- `GET /platform/security` (`platform.security`): Security invariants & audit
- `GET /platform/ai-governance` (`platform.ai-governance`): LLM model boundaries & guardrails
- `GET /platform/settings` (`platform.settings`): Platform configuration

### B. Tenant Administration
- `GET /admin` (`admin.index`): Redirects to Tenant Dashboard
- `GET /admin/dashboard` (`admin.dashboard`): Organization Overview
- `GET /admin/departments` (`admin.departments`): Department architecture
- `GET /admin/positions` (`admin.positions`): Job positions & codes
- `GET /admin/users` (`admin.users`): User accounts & access control
- `GET /admin/workflows` (`admin.workflows`): Approvals configuration
- `GET /admin/settings` (`admin.settings`): Tenant customization

### C. HR Operations
- `GET /hr` (`hr.index`): Redirects to HR Command Center
- `GET /hr/dashboard` (`hr.dashboard`): HR Command Center

### D. Manager Workspace
- `GET /manager` (`manager.index`): Redirects to Team Workbench
- `GET /manager/workbench` (`manager.workbench`): Team Workbench
- `GET /manager/members` (`manager.members`): Team Roster
- `GET /manager/performance` (`manager.performance`): Performance reviews
- `GET /manager/analytics` (`manager.analytics`): Team analytics

### E. Employee Self-Service
- `GET /employee` (`employee.index`): Redirects to Employee Home
- `GET /employee/home` (`employee.home`): Personal Home Workbench
- `GET /portal` (`portal.dashboard`): Legacy alias to Employee Home
- `GET /portal/work` (`portal.work`): Today's Shift & Punch
- `GET /portal/requests` (`portal.requests`): Leave requests
- `GET /portal/profile` (`portal.profile`): Personal profile
- `GET /portal/pay` (`portal.pay`): Payslips
- `GET /portal/growth` (`portal.growth`): Learning
- `GET /portal/documents` (`portal.documents`): Policy documents
- `GET /portal/services` (`portal.services`): Service catalog
- `GET /portal/directory` (`portal.directory`): People directory

### F. Executive Workspace
- `GET /executive` (`executive.index`): Redirects to Executive Overview
- `GET /executive/overview` (`executive.overview`): Workforce intelligence
- `GET /executive/costs` (`executive.costs`): Workforce costs

### G. System Operations
- `GET /operations/system-health` (`operations.system-health`): System health monitor
- `GET /operations/queues` (`operations.queues`): Queue infrastructure
- `GET /operations/logs` (`operations.logs`): Operational logs
