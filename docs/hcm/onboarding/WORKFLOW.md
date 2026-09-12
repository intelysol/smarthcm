# Onboarding Workflow & Escalations

## Workflow Engine Integration
Onboarding leverages the platform Workflow engine for:
- Document verification escalations
- Probation extension approvals
- IT equipment and provisioning approvals

## Automated Overdue Detection
`ProcessOnboardingTaskOverdueJob` executes periodically to identify tasks with `due_date < now()` and appends escalation notes for HR and managerial review.
