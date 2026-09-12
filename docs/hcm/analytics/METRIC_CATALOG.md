# Central Metric & KPI Catalog

| Metric Code | Name | Category | Unit | Aggregation | Sensitivity | Formula / Calculation |
|---|---|---|---|---|---|---|
| `HCM_HEADCOUNT_TOTAL` | Total Headcount | Workforce | Count | Count | Public | `COUNT(all_employees)` |
| `HCM_HEADCOUNT_ACTIVE` | Active Headcount | Workforce | Count | Count | Public | `COUNT(status = active)` |
| `HCM_FTE_TOTAL` | Full-Time Equivalent (FTE) | Workforce | Count | Sum | Public | `SUM(fte_weight)` |
| `HCM_TURNOVER_RATE` | Turnover Rate | Turnover | Percentage | Average | Public | `(Total Exits / Average Headcount) * 100` |
| `HCM_VOLUNTARY_TURNOVER` | Voluntary Turnover | Turnover | Percentage | Average | Public | `(Voluntary Exits / Average Headcount) * 100` |
| `HCM_TIME_TO_HIRE` | Average Time to Hire | Recruitment | Days | Average | Public | `AVG(offer_accepted_date - req_open_date)` |
| `HCM_OFFER_ACCEPTANCE_RATE`| Offer Acceptance Rate | Recruitment | Percentage | Average | Public | `(Accepted Offers / Total Offers) * 100` |
| `HCM_ATTENDANCE_RATE` | Attendance Rate | Attendance | Percentage | Average | Public | `(Present Days / Scheduled Work Days) * 100` |
| `HCM_ABSENTEEISM_RATE` | Absenteeism Rate | Attendance | Percentage | Average | Public | `(Unplanned Absent Days / Scheduled Days) * 100` |
| `HCM_GROSS_PAYROLL_TOTAL` | Gross Payroll Cost | Payroll | Currency | Sum | Sensitive | `SUM(gross_earnings)` |
| `HCM_AVG_SALARY` | Average Employee Salary | Compensation | Currency | Average | Sensitive | `AVG(base_salary)` |
| `HCM_ENPS_SCORE` | Employee Net Promoter Score | Engagement | Score | Average | Sensitive | `% Promoters - % Detractors` |
| `HCM_HR_SERVICE_SLA_COMPLIANCE`| Service SLA Compliance | HR Service | Percentage | Average | Public | `(Resolved Within SLA / Total Resolved) * 100` |
