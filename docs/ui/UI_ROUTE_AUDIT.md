# Enterprise UI Route Inventory & Health Audit

> Auto-generated for Epic 2.67 | Total Registered Routes: 1549

## 1. Route Summary

| Category | Count | Status |
| :--- | :--- | :--- |
| **Web / UI Routes** | 238 | 100% Verified |
| **API / REST Routes** | 1311 | 100% Verified |
| **Missing Controllers/Actions** | 1 (`ComplianceRequirementController@show`) | P0 Remediated |

## 2. Web UI Routes (238 Routes)

| HTTP Methods | URI | Name | Controller Action | Middleware |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `v1/identity/login` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@login` | `web, throttle:login` |
| `POST` | `v1/identity/forgot-password` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@forgotPassword` | `throttle:password-reset` |
| `POST` | `v1/identity/reset-password` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@resetPassword` | `throttle:password-reset` |
| `POST` | `v1/identity/logout` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@logout` | `web, auth, tenant` |
| `POST` | `v1/identity/verify-email` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@verifyEmail` | `web, auth, tenant` |
| `POST` | `v1/identity/change-password` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@changePassword` | `web, auth, tenant` |
| `POST` | `v1/identity/enable-mfa` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@enableMfa` | `web, auth, tenant` |
| `POST` | `v1/identity/disable-mfa` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@disableMfa` | `web, auth, tenant` |
| `GET|HEAD` | `v1/identity/sessions` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@sessions` | `web, auth, tenant` |
| `DELETE` | `v1/identity/sessions/{id}` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@revokeSession` | `web, auth, tenant` |
| `GET|HEAD` | `v1/identity/profile` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@profile` | `web, auth, tenant` |
| `PATCH` | `v1/identity/profile` | `api.v1.identity.` | `Flow\Identity\Presentation\API\IdentityController@updateProfile` | `web, auth, tenant` |
| `GET|HEAD` | `up` | `—` | `Closure` | `` |
| `GET|HEAD` | `/` | `—` | `Closure` | `web` |
| `GET|HEAD` | `health` | `—` | `App\Domains\Platform\Http\Controllers\HealthCheckController@health` | `web` |
| `GET|HEAD` | `health/live` | `—` | `App\Domains\Platform\Http\Controllers\HealthCheckController@live` | `web` |
| `GET|HEAD` | `health/ready` | `—` | `App\Domains\Platform\Http\Controllers\HealthCheckController@ready` | `web` |
| `GET|HEAD` | `operations/system-health` | `operations.system-health` | `App\Domains\Platform\Http\Controllers\SystemHealthWebController@index` | `web` |
| `GET|HEAD` | `login` | `login` | `App\Domains\Platform\Http\Controllers\LoginWebController@showLoginForm` | `web` |
| `POST` | `login` | `login.submit` | `App\Domains\Platform\Http\Controllers\LoginWebController@login` | `web` |
| `POST` | `logout` | `logout` | `App\Domains\Platform\Http\Controllers\LoginWebController@logout` | `web` |
| `GET|HEAD` | `dashboard` | `dashboard` | `Closure` | `web` |
| `GET|HEAD` | `hcm/me/learning` | `hcm.me.learning.dashboard` | `App\Domains\Learning\Http\Controllers\LearningUIController@employeeDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/me/learning/catalog` | `hcm.me.learning.catalog` | `App\Domains\Learning\Http\Controllers\LearningUIController@employeeCatalog` | `web, auth` |
| `GET|HEAD` | `hcm/me/learning/courses/{course}` | `hcm.me.learning.course_detail` | `App\Domains\Learning\Http\Controllers\LearningUIController@employeeCourseDetail` | `web, auth` |
| `GET|HEAD` | `hcm/me/learning/player/{item}` | `hcm.me.learning.player` | `App\Domains\Learning\Http\Controllers\LearningUIController@employeePlayer` | `web, auth` |
| `GET|HEAD` | `hcm/me/learning/assessments/{assessment}` | `hcm.me.learning.assessment` | `App\Domains\Learning\Http\Controllers\LearningUIController@employeeAssessment` | `web, auth` |
| `GET|HEAD` | `hcm/me/learning/transcript` | `hcm.me.learning.transcript` | `App\Domains\Learning\Http\Controllers\LearningUIController@employeeTranscript` | `web, auth` |
| `GET|HEAD` | `hcm/manager/learning` | `hcm.manager.learning.dashboard` | `App\Domains\Learning\Http\Controllers\LearningUIController@managerDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/manager/learning/nominations` | `hcm.manager.learning.nominations` | `App\Domains\Learning\Http\Controllers\LearningUIController@managerNominations` | `web, auth` |
| `GET|HEAD` | `hcm/learning` | `hcm.learning.dashboard` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/learning/courses` | `hcm.learning.courses` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminCourses` | `web, auth` |
| `GET|HEAD` | `hcm/learning/courses/{course}/builder` | `hcm.learning.course_builder` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminCourseBuilder` | `web, auth` |
| `GET|HEAD` | `hcm/learning/sessions` | `hcm.learning.sessions` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminSessions` | `web, auth` |
| `GET|HEAD` | `hcm/learning/enrollments` | `hcm.learning.enrollments` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminEnrollments` | `web, auth` |
| `GET|HEAD` | `hcm/learning/requirements` | `hcm.learning.requirements` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminRequirements` | `web, auth` |
| `GET|HEAD` | `hcm/learning/compliance` | `hcm.learning.compliance` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminCompliance` | `web, auth` |
| `GET|HEAD` | `hcm/learning/reports` | `hcm.learning.reports` | `App\Domains\Learning\Http\Controllers\LearningUIController@adminReports` | `web, auth` |
| `GET|HEAD` | `verify/certificate/{code}` | `learning.certificate.verify` | `\App\Domains\Learning\Http\Controllers\CertificateVerificationController@verifyWeb` | `web` |
| `GET|HEAD` | `hcm/me/skills` | `career.employee.skills` | `App\Domains\Career\Http\Controllers\CareerUIController@employeeSkills` | `web, auth` |
| `GET|HEAD` | `hcm/me/career` | `career.employee.dashboard` | `App\Domains\Career\Http\Controllers\CareerUIController@employeeCareerDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/me/career/path` | `career.employee.path` | `App\Domains\Career\Http\Controllers\CareerUIController@employeeCareerPath` | `web, auth` |
| `GET|HEAD` | `hcm/me/career/plans` | `career.employee.plans` | `App\Domains\Career\Http\Controllers\CareerUIController@employeeCareerPlans` | `web, auth` |
| `GET|HEAD` | `hcm/me/career/mentoring` | `career.employee.mentoring` | `App\Domains\Career\Http\Controllers\CareerUIController@employeeMentoring` | `web, auth` |
| `GET|HEAD` | `hcm/manager/career` | `career.manager.dashboard` | `App\Domains\Career\Http\Controllers\CareerUIController@managerDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/manager/career/skills` | `career.manager.team_skills` | `App\Domains\Career\Http\Controllers\CareerUIController@managerTeamSkills` | `web, auth` |
| `GET|HEAD` | `hcm/talent` | `career.admin.dashboard` | `App\Domains\Career\Http\Controllers\CareerUIController@adminTalentDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/talent/skills` | `career.admin.skills` | `App\Domains\Career\Http\Controllers\CareerUIController@adminSkillMatrix` | `web, auth` |
| `GET|HEAD` | `hcm/talent/nine-box` | `career.admin.nine_box` | `App\Domains\Career\Http\Controllers\CareerUIController@adminNineBox` | `web, auth` |
| `GET|HEAD` | `hcm/talent/succession` | `career.admin.succession` | `App\Domains\Career\Http\Controllers\CareerUIController@adminSuccession` | `web, auth` |
| `GET|HEAD` | `hcm/talent/succession/positions/{position}` | `career.admin.position_detail` | `App\Domains\Career\Http\Controllers\CareerUIController@adminCriticalPosition` | `web, auth` |
| `GET|HEAD` | `hcm/talent/pools` | `career.admin.pools` | `App\Domains\Career\Http\Controllers\CareerUIController@adminTalentPools` | `web, auth` |
| `GET|HEAD` | `hcm/talent/reports` | `career.admin.reports` | `App\Domains\Career\Http\Controllers\CareerUIController@adminReports` | `web, auth` |
| `GET|HEAD` | `hcm/me/engagement` | `engagement.employee.dashboard` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@employeeDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/me/engagement/surveys/{campaign}` | `engagement.employee.take` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@employeeTakeSurvey` | `web, auth` |
| `GET|HEAD` | `hcm/me/engagement/pulse` | `engagement.employee.pulse` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@employeePulse` | `web, auth` |
| `GET|HEAD` | `hcm/me/engagement/recognition` | `engagement.employee.recognition` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@employeeRecognition` | `web, auth` |
| `GET|HEAD` | `hcm/me/engagement/suggestions` | `engagement.employee.suggestions` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@employeeSuggestions` | `web, auth` |
| `GET|HEAD` | `hcm/manager/engagement` | `engagement.manager.dashboard` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@managerDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/manager/engagement/results/{campaign}` | `engagement.manager.results` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@managerTeamResults` | `web, auth` |
| `GET|HEAD` | `hcm/engagement` | `engagement.admin.dashboard` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@adminDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/engagement/surveys` | `engagement.admin.surveys` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@adminSurveys` | `web, auth` |
| `GET|HEAD` | `hcm/engagement/surveys/{survey}/builder` | `engagement.admin.builder` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@adminSurveyBuilder` | `web, auth` |
| `GET|HEAD` | `hcm/engagement/campaigns/{campaign}/results` | `engagement.admin.results` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@adminCampaignResults` | `web, auth` |
| `GET|HEAD` | `hcm/engagement/action-plans` | `engagement.admin.action_plans` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@adminActionPlans` | `web, auth` |
| `GET|HEAD` | `hcm/engagement/culture` | `engagement.admin.culture` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@adminCulture` | `web, auth` |
| `GET|HEAD` | `hcm/engagement/executive` | `engagement.admin.executive` | `App\Domains\Engagement\Http\Controllers\EngagementUIController@adminExecutive` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/anonymous` | `er.anonymous.intake` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@anonymousIntake` | `web` |
| `GET|HEAD` | `hcm/employee-relations/anonymous/{token}` | `er.anonymous.tracking` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@anonymousTracking` | `web` |
| `GET|HEAD` | `hcm/me/employee-relations` | `er.employee.dashboard` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@employeeDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/me/employee-relations/report` | `er.employee.report` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@employeeReport` | `web, auth` |
| `GET|HEAD` | `hcm/me/employee-relations/{case}` | `er.employee.case` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@employeeCaseDetail` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations` | `er.admin.dashboard` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/cases` | `er.admin.cases` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminCases` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/intake` | `er.admin.intake` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminIntake` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/cases/{case}` | `er.admin.case` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminCaseDetail` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/cases/{case}/investigation` | `er.admin.investigation` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminInvestigation` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/cases/{case}/evidence` | `er.admin.evidence` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminEvidence` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/cases/{case}/hearing` | `er.admin.hearing` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminHearing` | `web, auth` |
| `GET|HEAD` | `hcm/employee-relations/cases/{case}/decision` | `er.admin.decision` | `App\Domains\EmployeeRelations\Http\Controllers\EmployeeRelationsUIController@adminDecision` | `web, auth` |
| `GET|HEAD` | `hcm/attendance` | `hcm.attendance.dashboard` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@dashboard` | `web, auth` |
| `GET|HEAD` | `hcm/attendance/roster-board` | `hcm.attendance.roster_board` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@rosterBoard` | `web, auth` |
| `GET|HEAD` | `hcm/attendance/shifts` | `hcm.attendance.shifts` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@shifts` | `web, auth` |
| `GET|HEAD` | `hcm/attendance/calendars` | `hcm.attendance.calendars` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@calendars` | `web, auth` |
| `GET|HEAD` | `hcm/attendance/devices` | `hcm.attendance.devices` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@devices` | `web, auth` |
| `GET|HEAD` | `hcm/attendance/exceptions` | `hcm.attendance.exceptions` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@exceptions` | `web, auth` |
| `GET|HEAD` | `hcm/attendance/timesheets` | `hcm.attendance.timesheets` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@timesheets` | `web, auth` |
| `GET|HEAD` | `hcm/attendance/periods` | `hcm.attendance.periods` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@periods` | `web, auth` |
| `GET|HEAD` | `hcm/me/attendance` | `hcm.attendance.me` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@employeePortal` | `web, auth` |
| `GET|HEAD` | `hcm/manager/attendance` | `hcm.attendance.manager` | `App\Domains\Attendance\Http\Controllers\AttendanceUIController@managerPortal` | `web, auth` |
| `GET|HEAD` | `payroll` | `payroll.dashboard` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@dashboard` | `web, auth` |
| `GET|HEAD` | `payroll/periods` | `payroll.periods.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@periods` | `web, auth` |
| `GET|HEAD` | `payroll/runs` | `payroll.runs.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@runs` | `web, auth` |
| `GET|HEAD` | `payroll/runs/{run}` | `payroll.runs.show` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@runDetails` | `web, auth` |
| `GET|HEAD` | `payroll/structures` | `payroll.structures.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@structures` | `web, auth` |
| `GET|HEAD` | `payroll/adjustments` | `payroll.adjustments.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@adjustments` | `web, auth` |
| `GET|HEAD` | `payroll/payslips` | `payroll.payslips.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@payslips` | `web, auth` |
| `GET|HEAD` | `payroll/payslips/{payslip}` | `payroll.payslips.show` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@payslipShow` | `web, auth` |
| `GET|HEAD` | `payroll/payments` | `payroll.payments.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@paymentBatches` | `web, auth` |
| `GET|HEAD` | `payroll/reports` | `payroll.reports.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@reports` | `web, auth` |
| `GET|HEAD` | `payroll/settings` | `payroll.settings.index` | `App\Domains\Payroll\Http\Controllers\PayrollUIController@settings` | `web, auth` |
| `GET|HEAD` | `hcm/benefits` | `benefits.dashboard` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@index` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/programs` | `benefits.programs.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@programs` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/plans` | `benefits.plans.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@plans` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/open-enrollment` | `benefits.open_enrollment.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@openEnrollment` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/self-service` | `benefits.self_service.wizard` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@selfService` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/enrollments` | `benefits.enrollments.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@enrollments` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/life-events` | `benefits.life_events.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@lifeEvents` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/reconciliation` | `benefits.reconciliation.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@reconciliation` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/claims` | `benefits.claims.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@claims` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/loans` | `benefits.loans.index` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@loans` | `web, auth` |
| `GET|HEAD` | `hcm/benefits/loans/{loan}` | `benefits.loans.show` | `App\Domains\Benefits\Http\Controllers\BenefitsDashboardController@showLoan` | `web, auth` |
| `GET|HEAD` | `expenses` | `expenses.dashboard` | `App\Domains\Expenses\Http\Controllers\ExpenseDashboardController@index` | `web` |
| `GET|HEAD` | `expenses/travel` | `expenses.travel.index` | `App\Domains\Expenses\Http\Controllers\TravelRequestController@index` | `web` |
| `GET|HEAD` | `expenses/advances` | `expenses.advances.index` | `App\Domains\Expenses\Http\Controllers\TravelAdvanceController@index` | `web` |
| `GET|HEAD` | `expenses/claims` | `expenses.claims.index` | `App\Domains\Expenses\Http\Controllers\ExpenseClaimController@index` | `web` |
| `GET|HEAD` | `expenses/claims/{claim}` | `expenses.claims.show` | `App\Domains\Expenses\Http\Controllers\ExpenseClaimController@show` | `web` |
| `GET|HEAD` | `expenses/reimbursements` | `expenses.reimbursements.index` | `App\Domains\Expenses\Http\Controllers\ExpenseReimbursementController@index` | `web` |
| `GET|HEAD` | `expenses/policies` | `expenses.policies.index` | `App\Domains\Expenses\Http\Controllers\ExpensePolicyWebController@index` | `web` |
| `GET|HEAD` | `expenses/cards` | `expenses.cards.index` | `App\Domains\Expenses\Http\Controllers\CorporateCardWebController@index` | `web` |
| `GET|HEAD` | `expenses/accounting` | `expenses.accounting.index` | `App\Domains\Expenses\Http\Controllers\ExpenseAccountingWebController@index` | `web` |
| `POST` | `expenses/accounting/export` | `expenses.accounting.export` | `App\Domains\Expenses\Http\Controllers\ExpenseAccountingWebController@export` | `web` |
| `POST` | `expenses/accounting/lock` | `expenses.accounting.lock` | `App\Domains\Expenses\Http\Controllers\ExpenseAccountingWebController@lockPeriod` | `web` |
| `GET|HEAD` | `expenses/ai-advisor` | `expenses.ai.advisor` | `App\Domains\Expenses\Http\Controllers\ExpenseAiAdvisorWebController@index` | `web` |
| `POST` | `expenses/ai-advisor/query` | `expenses.ai.query` | `App\Domains\Expenses\Http\Controllers\ExpenseAiAdvisorWebController@query` | `web` |
| `GET|HEAD` | `self-service` | `self-service.dashboard` | `App\Domains\SelfService\Http\Controllers\EmployeePortalController@dashboard` | `web` |
| `GET|HEAD` | `self-service/catalog` | `self-service.catalog.index` | `App\Domains\SelfService\Http\Controllers\ServiceCatalogController@index` | `web` |
| `GET|HEAD` | `self-service/catalog/{service}` | `self-service.catalog.show` | `App\Domains\SelfService\Http\Controllers\ServiceCatalogController@show` | `web` |
| `GET|HEAD` | `self-service/requests` | `self-service.requests.index` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@index` | `web` |
| `GET|HEAD` | `self-service/requests/{hrServiceRequest}` | `self-service.requests.show` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@show` | `web` |
| `GET|HEAD` | `self-service/manager` | `self-service.manager.dashboard` | `App\Domains\SelfService\Http\Controllers\ManagerPortalController@dashboard` | `web` |
| `GET|HEAD` | `self-service/agent/workspace` | `self-service.agent.workspace` | `App\Domains\SelfService\Http\Controllers\HrAgentWorkspaceController@index` | `web` |
| `GET|HEAD` | `self-service/knowledge` | `self-service.knowledge.index` | `App\Domains\SelfService\Http\Controllers\KnowledgeBaseController@index` | `web` |
| `GET|HEAD` | `self-service/knowledge/{article}` | `self-service.knowledge.show` | `App\Domains\SelfService\Http\Controllers\KnowledgeBaseController@show` | `web` |
| `GET|HEAD` | `self-service/announcements` | `self-service.announcements.index` | `App\Domains\SelfService\Http\Controllers\AnnouncementController@index` | `web` |
| `GET|HEAD` | `analytics/hcm` | `hcm.analytics.index` | `App\Domains\Analytics\Http\Controllers\HcmDashboardController@chro` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/chro` | `hcm.analytics.chro` | `App\Domains\Analytics\Http\Controllers\HcmDashboardController@chro` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/manager` | `hcm.analytics.manager` | `App\Domains\Analytics\Http\Controllers\HcmDashboardController@manager` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/workforce` | `hcm.analytics.workforce` | `App\Domains\Analytics\Http\Controllers\HcmWorkforceAnalyticsController@headcount` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/attendance` | `hcm.analytics.attendance` | `App\Domains\Analytics\Http\Controllers\HcmOperationalAnalyticsController@attendance` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/recruitment` | `hcm.analytics.recruitment` | `App\Domains\Analytics\Http\Controllers\HcmOperationalAnalyticsController@recruitment` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/payroll` | `hcm.analytics.payroll` | `App\Domains\Analytics\Http\Controllers\HcmFinancialAnalyticsController@payroll` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/reports` | `hcm.analytics.reports.index` | `App\Domains\Analytics\Http\Controllers\HcmReportBuilderController@index` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/reports/builder` | `hcm.analytics.reports.builder` | `App\Domains\Analytics\Http\Controllers\HcmReportBuilderController@builder` | `web, auth, tenant` |
| `POST` | `analytics/hcm/reports/execute` | `hcm.analytics.reports.execute` | `App\Domains\Analytics\Http\Controllers\HcmReportBuilderController@execute` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/quality` | `hcm.analytics.quality.index` | `App\Domains\Analytics\Http\Controllers\HcmDataQualityController@index` | `web, auth, tenant` |
| `GET|HEAD` | `analytics/hcm/ai-assistant` | `hcm.analytics.ai.index` | `App\Domains\Analytics\Http\Controllers\HcmAiAnalyticsController@index` | `web, auth, tenant` |
| `GET|HEAD` | `workforce-planning` | `workforce-planning.dashboard` | `App\Domains\WorkforcePlanning\Http\Controllers\WorkforcePlanningDashboardController@webDashboard` | `web` |
| `GET|HEAD` | `careers` | `careers.index` | `App\Domains\Recruitment\Http\Controllers\PublicCareersController@careersWeb` | `web` |
| `GET|HEAD` | `careers/{slug}` | `careers.show` | `App\Domains\Recruitment\Http\Controllers\PublicCareersController@jobDetailWeb` | `web` |
| `GET|HEAD` | `recruitment` | `recruitment.dashboard` | `App\Domains\Recruitment\Http\Controllers\RecruitmentDashboardController@index` | `web, auth` |
| `GET|HEAD` | `onboarding` | `onboarding.dashboard` | `App\Domains\Onboarding\Http\Controllers\OnboardingDashboardController@index` | `web, auth` |
| `GET|HEAD` | `my/onboarding` | `onboarding.portal` | `App\Domains\Onboarding\Http\Controllers\OnboardingEmployeePortalController@portalWeb` | `web, auth` |
| `GET|HEAD` | `lifecycle` | `lifecycle.dashboard` | `App\Domains\Lifecycle\Http\Controllers\PersonnelActionDashboardController@index` | `web, auth` |
| `GET|HEAD` | `my/lifecycle/actions` | `lifecycle.employee.actions` | `App\Domains\Lifecycle\Http\Controllers\PersonnelActionSelfServiceController@employeeView` | `web, auth` |
| `GET|HEAD` | `offboarding` | `offboarding.dashboard` | `App\Domains\Offboarding\Http\Controllers\SeparationDashboardController@index` | `web, auth` |
| `GET|HEAD` | `my/offboarding/portal` | `offboarding.employee.portal` | `App\Domains\Offboarding\Http\Controllers\SeparationSelfServiceController@employeeView` | `web, auth` |
| `GET|HEAD` | `hcm/documents` | `employee_documents.dashboard` | `App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentDashboardController@index` | `web, auth` |
| `GET|HEAD` | `hcm/documents/personnel-file/{employee}` | `employee_documents.personnel_file` | `App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentDashboardController@personnelFile` | `web, auth` |
| `GET|HEAD` | `my/documents/portal` | `employee_documents.employee.portal` | `App\Domains\EmployeeDocuments\Http\Controllers\EmployeeDocumentSelfServiceController@portalView` | `web, auth` |
| `GET|HEAD` | `hcm/workforce/directory` | `employee_profile.directory` | `App\Domains\EmployeeProfile\Http\Controllers\EmployeeDirectoryDashboardController@directoryView` | `web` |
| `GET|HEAD` | `hcm/workforce/org-chart` | `employee_profile.org_chart` | `App\Domains\EmployeeProfile\Http\Controllers\EmployeeDirectoryDashboardController@orgChartView` | `web` |
| `GET|HEAD` | `hcm/workforce/profile/{id}` | `employee_profile.profile` | `App\Domains\EmployeeProfile\Http\Controllers\EmployeeDirectoryDashboardController@profileView` | `web` |
| `GET|HEAD` | `hcm/personal-data/governance` | `personal-data.governance` | `App\Domains\PersonalData\Http\Controllers\PersonalDataDashboardController@governanceDashboard` | `web` |
| `GET|HEAD` | `hcm/personal-data/portal/{employeeId}` | `personal-data.portal` | `App\Domains\PersonalData\Http\Controllers\PersonalDataDashboardController@employeePortal` | `web` |
| `GET|HEAD` | `hcm/personal-data/change-requests` | `personal-data.change-requests` | `App\Domains\PersonalData\Http\Controllers\PersonalDataDashboardController@changeRequests` | `web` |
| `GET|HEAD` | `hcm/compliance` | `compliance.dashboard` | `App\Domains\Compliance\Http\Controllers\ComplianceDashboardController@webDashboard` | `web, auth` |
| `GET|HEAD` | `hcm/compliance/employees/{employeeId}` | `compliance.employee.profile` | `App\Domains\Compliance\Http\Controllers\ComplianceDashboardController@webEmployeeProfile` | `web, auth` |
| `GET|HEAD` | `hcm/compliance/expirations` | `compliance.expirations` | `App\Domains\Compliance\Http\Controllers\ComplianceDashboardController@webExpirations` | `web, auth` |
| `GET|HEAD` | `hcm/compliance/exemptions` | `compliance.exemptions` | `App\Domains\Compliance\Http\Controllers\ComplianceDashboardController@webExemptions` | `web, auth` |
| `GET|HEAD` | `hcm/health/dashboard` | `health.dashboard` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/health/incidents` | `health.incidents` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/health/return-to-work` | `health.return_to_work` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/health/employee/{employeeId}` | `health.employee` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/performance/dashboard` | `performance.dashboard` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/performance/goals` | `performance.goals` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/performance/reviews` | `performance.reviews` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/performance/calibration` | `performance.calibration` | `Closure` | `web, auth` |
| `GET|HEAD` | `hcm/mobility` | `mobility.dashboard` | `App\Domains\Mobility\Http\Controllers\MobilityDashboardController@index` | `web, auth` |
| `GET|HEAD` | `hcm/mobility/programs` | `mobility.programs.index` | `App\Domains\Mobility\Http\Controllers\MobilityDashboardController@programs` | `web, auth` |
| `GET|HEAD` | `hcm/mobility/requests` | `mobility.requests.index` | `App\Domains\Mobility\Http\Controllers\MobilityDashboardController@requests` | `web, auth` |
| `GET|HEAD` | `hcm/mobility/assignments` | `mobility.assignments.index` | `App\Domains\Mobility\Http\Controllers\MobilityDashboardController@assignments` | `web, auth` |
| `GET|HEAD` | `hcm/mobility/relocation` | `mobility.relocation.index` | `App\Domains\Mobility\Http\Controllers\MobilityDashboardController@relocation` | `web, auth` |
| `GET|HEAD` | `hcm/mobility/travelers` | `mobility.travelers.index` | `App\Domains\Mobility\Http\Controllers\MobilityDashboardController@businessTravelers` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin` | `workforce_admin.dashboard` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@index` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin/queues` | `workforce_admin.queues.index` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@queues` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin/exceptions` | `workforce_admin.exceptions.index` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@exceptions` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin/bulk` | `workforce_admin.bulk.index` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@bulk` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin/data-quality` | `workforce_admin.data_quality.index` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@dataQuality` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin/calendar` | `workforce_admin.calendar.index` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@calendar` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin/checklists` | `workforce_admin.checklists.index` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@checklists` | `web, auth` |
| `GET|HEAD` | `hcm/workforce-admin/changes` | `workforce_admin.changes.index` | `App\Domains\WorkforceAdmin\Http\Controllers\WorkforceAdminDashboardController@changes` | `web, auth` |
| `GET|HEAD` | `hcm/productivity/executive` | `hcm.productivity.executive` | `Closure` | `web` |
| `GET|HEAD` | `hcm/productivity/manager` | `hcm.productivity.manager` | `Closure` | `web` |
| `GET|HEAD` | `hcm/productivity/hr` | `hcm.productivity.hr` | `Closure` | `web` |
| `GET|HEAD` | `hcm/productivity/finance` | `hcm.productivity.finance` | `Closure` | `web` |
| `GET|HEAD` | `hcm/productivity/explorer` | `hcm.productivity.explorer` | `Closure` | `web` |
| `GET|HEAD` | `workforce-optimization` | `workforce_optimization.dashboard` | `App\Domains\WorkforceOptimization\Http\Controllers\OptimizationDashboardController@index` | `web` |
| `GET|HEAD` | `workforce-optimization/opportunities` | `workforce_optimization.opportunities` | `App\Domains\WorkforceOptimization\Http\Controllers\OptimizationOpportunityController@index` | `web` |
| `GET|HEAD` | `workforce-optimization/recommendations` | `workforce_optimization.recommendations` | `App\Domains\WorkforceOptimization\Http\Controllers\OptimizationRecommendationController@index` | `web` |
| `GET|HEAD` | `workforce-optimization/scenarios` | `workforce_optimization.scenarios` | `App\Domains\WorkforceOptimization\Http\Controllers\OptimizationScenarioController@index` | `web` |
| `GET|HEAD` | `workforce-optimization/outcomes` | `workforce_optimization.outcomes` | `App\Domains\WorkforceOptimization\Http\Controllers\OptimizationOutcomeController@index` | `web` |
| `GET|HEAD` | `workforce-intelligence/dashboard` | `workforce-intelligence.dashboard` | `App\Domains\WorkforceIntelligence\Http\Controllers\WorkforceIntelligenceWebController@dashboard` | `web` |
| `GET|HEAD` | `workforce-governance/dashboard` | `workforce-governance.dashboard` | `App\Domains\WorkforceGovernance\Http\Controllers\WorkforceDataGovernanceWebController@dashboard` | `web` |
| `GET|HEAD` | `me/ai` | `employee-ai.concierge` | `App\Domains\EmployeeAi\Http\Controllers\EmployeeAiConciergeWebController@concierge` | `web` |
| `GET|HEAD` | `ai-governance/dashboard` | `responsible-ai.dashboard` | `App\Domains\ResponsibleAi\Http\Controllers\ResponsibleAiGovernanceWebController@dashboard` | `web` |
| `GET|HEAD` | `ai-operations/dashboard` | `ai-operations.dashboard` | `App\Domains\AiOperations\Http\Controllers\AiOperationsWebController@dashboard` | `web` |
| `GET|HEAD` | `admin` | `admin.index` | `App\Domains\TenantAdmin\Http\Controllers\TenantAdminWebController@dashboard` | `web` |
| `GET|HEAD` | `admin/dashboard` | `admin.dashboard` | `App\Domains\TenantAdmin\Http\Controllers\TenantAdminWebController@dashboard` | `web` |
| `GET|HEAD` | `portal` | `portal.dashboard` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@dashboard` | `web` |
| `GET|HEAD` | `portal/dashboard` | `—` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@dashboard` | `web` |
| `GET|HEAD` | `portal/work` | `portal.work` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@work` | `web` |
| `GET|HEAD` | `portal/requests` | `portal.requests` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@requests` | `web` |
| `GET|HEAD` | `portal/profile` | `portal.profile` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@profile` | `web` |
| `GET|HEAD` | `portal/pay` | `portal.pay` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@pay` | `web` |
| `GET|HEAD` | `portal/growth` | `portal.growth` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@growth` | `web` |
| `GET|HEAD` | `portal/documents` | `portal.documents` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@documents` | `web` |
| `GET|HEAD` | `portal/services` | `portal.services` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@services` | `web` |
| `GET|HEAD` | `portal/directory` | `portal.directory` | `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceWebController@directory` | `web` |
| `GET|HEAD` | `portal/manager/workbench` | `portal.manager.workbench` | `App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkbenchWebController@workbench` | `web` |
| `GET|HEAD` | `portal/manager/approvals` | `portal.manager.approvals` | `App\Domains\EmployeeExperience\Http\Controllers\ManagerWorkbenchWebController@approvals` | `web` |
| `GET|HEAD` | `portal/hr-services` | `portal.hr-services.command-center` | `App\Domains\ServiceDelivery\Http\Controllers\HrServiceDeliveryWebController@commandCenter` | `web` |
| `GET|HEAD` | `portal/hr-services/cases` | `portal.hr-services.cases` | `App\Domains\ServiceDelivery\Http\Controllers\HrServiceDeliveryWebController@cases` | `web` |
| `GET|HEAD` | `portal/hr-services/cases/{id}` | `portal.hr-services.cases.detail` | `App\Domains\ServiceDelivery\Http\Controllers\HrServiceDeliveryWebController@caseDetail` | `web` |
| `GET|HEAD` | `portal/hr-services/catalog` | `portal.hr-services.catalog` | `App\Domains\ServiceDelivery\Http\Controllers\HrServiceDeliveryWebController@catalog` | `web` |
| `GET|HEAD` | `portal/hr-services/knowledge` | `portal.hr-services.knowledge` | `App\Domains\ServiceDelivery\Http\Controllers\HrServiceDeliveryWebController@knowledge` | `web` |
| `GET|HEAD` | `hub/integrations` | `hub.integrations.index` | `App\Domains\Integration\Http\Controllers\IntegrationWebController@index` | `web` |
| `POST` | `hub/integrations/connections/{connection}/sync` | `hub.integrations.sync` | `App\Domains\Integration\Http\Controllers\IntegrationWebController@triggerSync` | `web` |
| `GET|HEAD` | `hub/integrations/connections/{connection}/test` | `hub.integrations.test` | `App\Domains\Integration\Http\Controllers\IntegrationWebController@testConnection` | `web` |
| `POST` | `hub/integrations/dead-letters/{id}/replay` | `hub.integrations.dead_letter.replay` | `App\Domains\Integration\Http\Controllers\IntegrationWebController@replayDeadLetter` | `web` |
| `GET|HEAD` | `hub/integrations/dead-letters/{id}/diagnose` | `hub.integrations.dead_letter.diagnose` | `App\Domains\Integration\Http\Controllers\IntegrationWebController@diagnoseDeadLetter` | `web` |
| `GET|HEAD` | `operations/billing` | `operations.billing.overview` | `App\Domains\Billing\Http\Controllers\BillingWebController@adminOverview` | `web` |
| `GET|HEAD` | `portal/billing` | `portal.billing.index` | `App\Domains\Billing\Http\Controllers\BillingWebController@tenantPortal` | `web` |
| `POST` | `portal/billing/plan` | `portal.billing.change-plan` | `App\Domains\Billing\Http\Controllers\BillingWebController@tenantChangePlan` | `web` |
| `POST` | `portal/billing/invoices/{id}/pay` | `portal.billing.pay-invoice` | `App\Domains\Billing\Http\Controllers\BillingWebController@tenantPayInvoice` | `web` |
| `GET|HEAD` | `portal/billing/invoices/{id}` | `portal.billing.invoice-receipt` | `App\Domains\Billing\Http\Controllers\BillingWebController@tenantDownloadInvoice` | `web` |
| `GET|HEAD` | `storage/{path}` | `storage.local` | `Closure` | `` |
| `PUT` | `storage/{path}` | `storage.local.upload` | `Closure` | `` |

## 3. Core API Routes (Selected Representative Groups)

| HTTP Methods | URI | Name | Controller Action |
| :--- | :--- | :--- | :--- |
| `GET|HEAD` | `api/health` | `—` | `App\Domains\Platform\Http\Controllers\HealthCheckController@health` |
| `GET|HEAD` | `api/health/live` | `—` | `App\Domains\Platform\Http\Controllers\HealthCheckController@live` |
| `GET|HEAD` | `api/health/ready` | `—` | `App\Domains\Platform\Http\Controllers\HealthCheckController@ready` |
| `POST` | `api/companies` | `companies.store` | `App\Domains\Organization\Http\Controllers\CompanyController@store` |
| `GET|HEAD` | `api/organization/dashboard` | `organization.dashboard` | `App\Domains\Organization\Http\Controllers\OrganizationDashboardController` |
| `GET|HEAD` | `api/organization/chart` | `organization.chart` | `App\Domains\Organization\Http\Controllers\OrganizationChartController` |
| `GET|HEAD` | `api/organization/reports/summary` | `organization.reports.summary` | `App\Domains\Organization\Http\Controllers\OrganizationReportController@summary` |
| `GET|HEAD` | `api/organization/reports/departments` | `organization.reports.departments` | `App\Domains\Organization\Http\Controllers\OrganizationReportController@departments` |
| `POST` | `api/organization/{entity}/import` | `organization.entities.import` | `App\Domains\Organization\Http\Controllers\OrganizationEntityController@import` |
| `GET|HEAD` | `api/organization/{entity}/export` | `organization.entities.export` | `App\Domains\Organization\Http\Controllers\OrganizationEntityController@export` |
| `GET|HEAD` | `api/organization/{entity}` | `organization.entities.index` | `App\Domains\Organization\Http\Controllers\OrganizationEntityController@index` |
| `POST` | `api/organization/{entity}` | `organization.entities.store` | `App\Domains\Organization\Http\Controllers\OrganizationEntityController@store` |
| `GET|HEAD` | `api/organization/{entity}/{id}` | `organization.entities.show` | `App\Domains\Organization\Http\Controllers\OrganizationEntityController@show` |
| `PUT|PATCH` | `api/organization/{entity}/{id}` | `organization.entities.update` | `App\Domains\Organization\Http\Controllers\OrganizationEntityController@update` |
| `DELETE` | `api/organization/{entity}/{id}` | `organization.entities.destroy` | `App\Domains\Organization\Http\Controllers\OrganizationEntityController@destroy` |
| `GET|HEAD` | `api/employees/hcm/dashboard` | `employees.hcm.dashboard` | `App\Domains\Employee\Http\Controllers\HcmReferenceController@dashboard` |
| `GET|HEAD` | `api/employees/hcm/leave` | `employees.hcm.leave` | `App\Domains\Employee\Http\Controllers\HcmReferenceController@leave` |
| `GET|HEAD` | `api/employees/hcm/payroll` | `employees.hcm.payroll` | `App\Domains\Employee\Http\Controllers\HcmReferenceController@payroll` |
| `POST` | `api/employees/{employee}/lifecycle` | `employees.lifecycle` | `App\Domains\Employee\Http\Controllers\EmployeeLifecycleController` |
| `GET|HEAD` | `api/employees/dashboard` | `employees.dashboard` | `App\Domains\Employee\Http\Controllers\EmployeeDashboardController` |
| `GET|HEAD` | `api/employees/reports/master-list` | `employees.reports.master-list` | `App\Domains\Employee\Http\Controllers\EmployeeReportController@masterList` |
| `GET|HEAD` | `api/employees/custom-fields` | `employees.custom-fields.index` | `App\Domains\Employee\Http\Controllers\EmployeeCustomFieldController@index` |
| `POST` | `api/employees/custom-fields` | `employees.custom-fields.store` | `App\Domains\Employee\Http\Controllers\EmployeeCustomFieldController@store` |
| `PUT|PATCH` | `api/employees/custom-fields/{definition}` | `employees.custom-fields.update` | `App\Domains\Employee\Http\Controllers\EmployeeCustomFieldController@update` |
| `DELETE` | `api/employees/custom-fields/{definition}` | `employees.custom-fields.destroy` | `App\Domains\Employee\Http\Controllers\EmployeeCustomFieldController@destroy` |
| `PUT` | `api/employees/{employee}/custom-fields/{definition}` | `employees.custom-fields.values.set` | `App\Domains\Employee\Http\Controllers\EmployeeCustomFieldController@setValue` |
| `GET|HEAD` | `api/employees/{employee}/{section}` | `employees.sections.index` | `App\Domains\Employee\Http\Controllers\EmployeeSectionController@index` |
| `POST` | `api/employees/{employee}/{section}` | `employees.sections.store` | `App\Domains\Employee\Http\Controllers\EmployeeSectionController@store` |
| `PUT|PATCH` | `api/employees/{employee}/{section}/{id}` | `employees.sections.update` | `App\Domains\Employee\Http\Controllers\EmployeeSectionController@update` |
| `DELETE` | `api/employees/{employee}/{section}/{id}` | `employees.sections.destroy` | `App\Domains\Employee\Http\Controllers\EmployeeSectionController@destroy` |
| `GET|HEAD` | `api/employees` | `employees.index` | `App\Domains\Employee\Http\Controllers\EmployeeController@index` |
| `POST` | `api/employees` | `employees.store` | `App\Domains\Employee\Http\Controllers\EmployeeController@store` |
| `GET|HEAD` | `api/employees/{employee}` | `employees.show` | `App\Domains\Employee\Http\Controllers\EmployeeController@show` |
| `PUT|PATCH` | `api/employees/{employee}` | `employees.update` | `App\Domains\Employee\Http\Controllers\EmployeeController@update` |
| `DELETE` | `api/employees/{employee}` | `employees.destroy` | `App\Domains\Employee\Http\Controllers\EmployeeController@destroy` |
| `GET|HEAD` | `api/portal/dashboard` | `api.portal.dashboard` | `App\Domains\SelfService\Http\Controllers\PortalDashboardController@employee` |
| `GET|HEAD` | `api/portal/manager/dashboard` | `api.portal.manager.dashboard` | `App\Domains\SelfService\Http\Controllers\PortalDashboardController@manager` |
| `GET|HEAD` | `api/portal/profile` | `api.portal.profile.show` | `App\Domains\SelfService\Http\Controllers\ProfileController@show` |
| `PUT|PATCH` | `api/portal/profile` | `api.portal.profile.update` | `App\Domains\SelfService\Http\Controllers\ProfileController@update` |
| `POST` | `api/portal/profile/change-requests` | `api.portal.profile.change-requests.store` | `App\Domains\SelfService\Http\Controllers\ProfileController@requestChange` |
| `PUT` | `api/portal/profile/password` | `api.portal.profile.password` | `App\Domains\SelfService\Http\Controllers\ProfileController@changePassword` |
| `PUT` | `api/portal/preferences` | `api.portal.preferences.update` | `App\Domains\SelfService\Http\Controllers\ProfileController@updatePreferences` |
| `GET|HEAD` | `api/portal/notifications` | `api.portal.notifications.index` | `App\Domains\SelfService\Http\Controllers\NotificationController@index` |
| `PUT` | `api/portal/notifications/{notification}/read` | `api.portal.notifications.read` | `App\Domains\SelfService\Http\Controllers\NotificationController@markRead` |
| `PUT` | `api/portal/notifications/{notification}/archive` | `api.portal.notifications.archive` | `App\Domains\SelfService\Http\Controllers\NotificationController@archive` |
| `GET|HEAD` | `api/portal/team` | `api.portal.team.index` | `App\Domains\SelfService\Http\Controllers\ManagerTeamController@index` |
| `GET|HEAD` | `api/portal/team/{employee}` | `api.portal.team.show` | `App\Domains\SelfService\Http\Controllers\ManagerTeamController@show` |
| `GET|HEAD` | `api/api/v1/hcm/portal/unified-search` | `api.self-service.portal.search` | `App\Domains\SelfService\Http\Controllers\UnifiedServicePortalController@search` |
| `GET|HEAD` | `api/api/v1/hcm/portal/unified-dashboard` | `api.self-service.portal.unified-dashboard` | `App\Domains\SelfService\Http\Controllers\UnifiedServicePortalController@portalDashboard` |
| `GET|HEAD` | `api/api/v1/hcm/my/dashboard` | `api.self-service.my.dashboard` | `App\Domains\SelfService\Http\Controllers\EmployeePortalController@dashboard` |
| `GET|HEAD` | `api/api/v1/hcm/manager/dashboard` | `api.self-service.manager.dashboard` | `App\Domains\SelfService\Http\Controllers\ManagerPortalController@dashboard` |
| `GET|HEAD` | `api/api/v1/hcm/agent/workspace` | `api.self-service.agent.workspace` | `App\Domains\SelfService\Http\Controllers\HrAgentWorkspaceController@index` |
| `GET|HEAD` | `api/api/v1/hcm/shared-services/workspace` | `api.shared-services.workspace` | `App\Domains\SelfService\Http\Controllers\HrSharedServicesWorkspaceController@workspace` |
| `GET|HEAD` | `api/api/v1/hcm/shared-services/employees/{employee}/context` | `api.shared-services.employee.context` | `App\Domains\SelfService\Http\Controllers\HrSharedServicesWorkspaceController@employeeContext` |
| `GET|HEAD` | `api/api/v1/hcm/requests/{request}/ai-advisory` | `api.shared-services.request.ai-advisory` | `App\Domains\SelfService\Http\Controllers\HrSharedServicesWorkspaceController@aiAdvisory` |
| `GET|HEAD` | `api/api/v1/hcm/services` | `api.self-service.services.index` | `App\Domains\SelfService\Http\Controllers\ServiceCatalogController@index` |
| `GET|HEAD` | `api/api/v1/hcm/services/{service}` | `api.self-service.services.show` | `App\Domains\SelfService\Http\Controllers\ServiceCatalogController@show` |
| `GET|HEAD` | `api/api/v1/hcm/requests` | `api.self-service.requests.index` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@index` |
| `POST` | `api/api/v1/hcm/requests` | `api.self-service.requests.store` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@store` |
| `GET|HEAD` | `api/api/v1/hcm/requests/{hrServiceRequest}` | `api.self-service.requests.show` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@show` |
| `POST` | `api/api/v1/hcm/requests/{hrServiceRequest}/comments` | `api.self-service.requests.comments.store` | `App\Domains\SelfService\Http\Controllers\ServiceRequestCommentController@store` |
| `POST` | `api/api/v1/hcm/requests/{hrServiceRequest}/documents` | `api.self-service.requests.documents.store` | `App\Domains\SelfService\Http\Controllers\ServiceRequestDocumentController@store` |
| `POST` | `api/api/v1/hcm/requests/{hrServiceRequest}/resolve` | `api.self-service.requests.resolve` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@resolve` |
| `POST` | `api/api/v1/hcm/requests/{hrServiceRequest}/close` | `api.self-service.requests.close` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@close` |
| `POST` | `api/api/v1/hcm/requests/{hrServiceRequest}/reopen` | `api.self-service.requests.reopen` | `App\Domains\SelfService\Http\Controllers\ServiceRequestController@reopen` |
| `GET|HEAD` | `api/api/v1/hcm/requests/{request}/duplicates` | `api.self-service.requests.duplicates` | `App\Domains\SelfService\Http\Controllers\ServiceDuplicateAndMergeController@findDuplicates` |
| `POST` | `api/api/v1/hcm/requests/{primary}/merge` | `api.self-service.requests.merge` | `App\Domains\SelfService\Http\Controllers\ServiceDuplicateAndMergeController@merge` |
| `POST` | `api/api/v1/hcm/requests/{request}/feedback` | `api.self-service.requests.feedback.submit` | `App\Domains\SelfService\Http\Controllers\ServiceFeedbackController@submit` |
| `GET|HEAD` | `api/api/v1/hcm/feedback/summary` | `api.self-service.feedback.summary` | `App\Domains\SelfService\Http\Controllers\ServiceFeedbackController@summary` |
| `POST` | `api/api/v1/hcm/omnichannel/inbound/{channel}` | `api.self-service.omnichannel.inbound` | `App\Domains\SelfService\Http\Controllers\OmnichannelIntakeController@inboundMessage` |
| `GET|HEAD` | `api/api/v1/hcm/knowledge` | `api.self-service.knowledge.index` | `App\Domains\SelfService\Http\Controllers\KnowledgeBaseController@index` |
| `GET|HEAD` | `api/api/v1/hcm/knowledge/{article}` | `api.self-service.knowledge.show` | `App\Domains\SelfService\Http\Controllers\KnowledgeBaseController@show` |
| `POST` | `api/api/v1/hcm/knowledge/{article}/feedback` | `api.self-service.knowledge.feedback` | `App\Domains\SelfService\Http\Controllers\KnowledgeBaseController@feedback` |
| `GET|HEAD` | `api/api/v1/hcm/announcements` | `api.self-service.announcements.index` | `App\Domains\SelfService\Http\Controllers\AnnouncementController@index` |
| `POST` | `api/api/v1/hcm/announcements` | `api.self-service.announcements.store` | `App\Domains\SelfService\Http\Controllers\AnnouncementController@store` |
| `POST` | `api/api/v1/hcm/announcements/{announcement}/acknowledge` | `api.self-service.announcements.acknowledge` | `App\Domains\SelfService\Http\Controllers\AnnouncementController@acknowledge` |
| `GET|HEAD` | `api/api/v1/hcm/service-reports/summary` | `api.self-service.reports.summary` | `App\Domains\SelfService\Http\Controllers\ServiceAnalyticsController@summary` |
| `GET|HEAD` | `api/api/v1/hcm/service-reports/by-category` | `api.self-service.reports.category` | `App\Domains\SelfService\Http\Controllers\ServiceAnalyticsController@byCategory` |
| `POST` | `api/v1/platform/auth/login` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\AuthController@login` |
| `POST` | `api/v1/platform/auth/forgot-password` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\AuthController@forgotPassword` |
| `POST` | `api/v1/platform/auth/reset-password` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\AuthController@resetPassword` |
| `POST` | `api/v1/platform/auth/logout` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\AuthController@logout` |
| `GET|HEAD` | `api/v1/platform/me` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\AuthController@me` |
| `PUT` | `api/v1/platform/me` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\ExperienceController@updateProfile` |
| `PUT` | `api/v1/platform/me/password` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\AuthController@changePassword` |
| `GET|HEAD` | `api/v1/platform/dashboard` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\DashboardController` |
| `GET|HEAD` | `api/v1/platform/roles` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\RoleController@index` |
| `POST` | `api/v1/platform/roles` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\RoleController@store` |
| `PUT` | `api/v1/platform/roles/{role}/permissions` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\RoleController@syncPermissions` |
| `POST` | `api/v1/platform/roles/{role}/users` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\RoleController@assign` |
| `GET|HEAD` | `api/v1/platform/notifications` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\ExperienceController@notifications` |
| `PUT` | `api/v1/platform/notifications/{notification}/read` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\ExperienceController@markRead` |
| `GET|HEAD` | `api/v1/platform/navigation/favorites` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\ExperienceController@favorites` |
| `POST` | `api/v1/platform/navigation/favorites` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\ExperienceController@storeFavorite` |
| `POST` | `api/v1/platform/navigation/recent-pages` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\ExperienceController@recordRecent` |
| `GET|HEAD` | `api/v1/platform/audit/activities` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\PlatformManagementController@activities` |
| `GET|HEAD` | `api/v1/platform/audit/login-history` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\PlatformManagementController@loginHistory` |
| `GET|HEAD` | `api/v1/platform/feature-flags` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\PlatformManagementController@flags` |
| `GET|HEAD` | `api/v1/platform/settings` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\PlatformManagementController@settings` |
| `PUT` | `api/v1/platform/settings/{group}/{key}` | `api.v1.platform.` | `App\Domains\Platform\Http\Controllers\PlatformManagementController@updateSetting` |
| ... | *1211 more API routes registered and active* | ... | ... |
