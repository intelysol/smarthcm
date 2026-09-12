# Analytics Authorization & Row-Level Security

## Granular Analytics Permissions
- `hcm.analytics.view`: General access to analytics home and public KPI dashboards.
- `hcm.analytics.manage`: Configuration of metrics, KPI targets, alerts, and data quality rules.
- `hcm.analytics.payroll.view`: Dedicated permission for gross/net payroll, employer statutory costs, and compensation distributions.
- `hcm.analytics.er.aggregate.view`: Permission to view ER case trends and aging aggregates.
- `hcm.analytics.sensitive.view`: Permission for sensitive demographic and medical benefit analytics.
- `hcm.report.export`: Export permissions for CSV, XLSX, and PDF files.

## Manager Scope Filtering
Line managers accessing analytics dashboards are scoped strictly to their direct reporting chain (`reporting_manager_id` or `current_manager_employee_id`). Attempts to query non-reporting department or employee data via API parameters are blocked on the server.
