# Probation Management & Extension Lifecycle

## Probation Tracking
Upon onboarding initialization, `HcmOnboardingProbation` is generated with:
- `probation_start_date` = Employee start date
- `probation_end_date` = Default 90 days post start date
- Status: `in_progress`

## Extension & Review
- Approaching probations (within 14 days) are updated to `due` by `CheckProbationDueJob`.
- If extension is requested, `OnboardingProbationService::extendProbation` records `extended_to_date` and transitions status to `extended`.
- Review completion records performance ratings, manager recommendation (`pass`, `extend`, `fail`), and confirms formal employment status.
