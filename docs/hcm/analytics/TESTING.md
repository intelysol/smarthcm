# HCM Analytics Automated Test Strategy

## Feature Test Suites (`tests/Feature/Analytics/`)
1. `HcmMetricRegistryAndVersioningTest`: Metric cataloging, version creation, and calculation reproducibility across time.
2. `HcmWorkforceSnapshotAndHeadcountTest`: Point-in-time active/inactive headcount, FTE, and demographic grouping.
3. `HcmTurnoverAndRetentionAnalyticsTest`: Annualized turnover, voluntary/involuntary rates, and regrettable loss calculations.
4. `HcmRecruitmentAndOnboardingAnalyticsTest`: Recruitment funnel conversion rates and succession bench strength.
5. `HcmTimeAttendanceAndLeaveAnalyticsTest`: Attendance rate, absenteeism rate, overtime hours, and leave utilization.
6. `HcmPayrollCompensationAndExpenseAnalyticsTest`: Payroll cost sums, salary percentiles (min, p25, median, p75, max), and expense totals.
7. `HcmEngagementPrivacyAndErAggregateTest`: Anonymity threshold ($\ge 5$) suppression verification and ER aggregate-only data isolation.
8. `HcmReportBuilderAndExportTest`: Dynamic dataset querying, grouping, and CSV file export generation.
9. `HcmDataQualityAndAlertsTest`: Data integrity rule evaluation, health score computation, and metric threshold alert triggers.
10. `HcmAiAnalyticsAssistantTest`: Natural language query intent resolution and advisory summary generation without arbitrary SQL.
