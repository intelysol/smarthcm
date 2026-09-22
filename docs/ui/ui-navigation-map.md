# Enterprise Navigation Map & Information Architecture

## 1. Information Architecture by Workspace

### A. Platform Administration (`/platform/*`)
```text
Platform Control Center
├── Control Center (`/platform/control-center`)
├── Tenants & Provisioning (`/platform/tenants`)
├── Products & Subscriptions (`/platform/billing`)
├── Integrations Hub (`/platform/integrations`)
├── System Health & Queues (`/operations/system-health`)
├── Security & Audit (`/platform/security`)
├── AI Governance (`/platform/ai-governance`)
└── Platform Settings (`/platform/settings`)
```

### B. Tenant Administration Portal (`/admin/*`)
```text
Organization Administration
├── Organization Overview (`/admin/dashboard`)
├── Departments & Locations (`/admin/departments`)
├── Positions & Architecture (`/admin/positions`)
├── Users & Access Control (`/admin/users`)
├── Policies & Compliance (`/compliance/dashboard`)
├── Document Templates (`/employee-documents/admin/templates`)
├── Workflows & Approvals (`/admin/workflows`)
└── Organization Settings (`/admin/settings`)
```

### C. HR Operations Workspace (`/hr/*`)
```text
HR Operations Command Center
├── HR Dashboard (`/hr/dashboard`)
├── People Directory (`/portal/directory`)
├── Recruitment & Jobs (`/recruitment/requisitions`)
├── Onboarding (`/onboarding/programs`)
├── Time & Attendance (`/hcm/attendance`)
├── Payroll & Compensation (`/payroll`)
├── Benefits & Health (`/benefits/programs`)
├── Expenses Management (`/expenses/claims`)
├── Performance & Goals (`/performance/cycles`)
├── Learning & Training (`/learning/catalog`)
├── Employee Relations (`/employee-relations/cases`)
└── HR Reports & Analytics (`/analytics/workforce/metrics`)
```

### D. Manager Workspace (`/manager/*`)
```text
My Team (Manager Workbench)
├── Team Overview (`/manager/workbench`)
├── Team Roster (`/manager/members`)
├── Pending Approvals (`/portal/manager/approvals`)
├── Team Attendance & Shifts (`/hcm/manager/attendance`)
├── Team Reviews & Goals (`/manager/performance`)
└── Team Analytics (`/manager/analytics`)
```

### E. Employee Self-Service (`/employee/*`, `/portal/*`)
```text
My Digital Workplace
├── My Home (`/employee/home`, `/portal`)
├── My Work & Schedule (`/portal/work`)
├── My Leaves & Requests (`/portal/requests`)
├── My Profile & Information (`/portal/profile`)
├── My Payslips & Tax (`/portal/pay`)
├── My Growth & Learning (`/portal/growth`)
├── My Documents & Letters (`/portal/documents`)
├── HR Service Catalog (`/portal/services`)
└── Colleagues Directory (`/portal/directory`)
```

### F. Executive Workspace (`/executive/*`)
```text
Workforce Intelligence
├── Strategic Overview (`/executive/overview`)
├── Headcount & Retention (`/analytics/workforce/metrics`)
├── Total Workforce Cost (`/executive/costs`)
├── Productivity & Capacity (`/workforce-productivity`)
├── Strategic Planning (`/workforce-planning`)
└── Workforce Intelligence (`/workforce-intelligence`)
```

### G. System Operations (`/operations/*`)
```text
System Operations
├── System Health & Metrics (`/operations/system-health`)
├── Queues & Horizon (`/operations/queues`)
├── Commercial Billing & Invoices (`/portal/billing`)
├── Biometric Devices (`/hcm/attendance/devices`)
└── Operational Logs & Telemetry (`/operations/logs`)
```
