# Onboarding Templates & Rule-Based Assignment

## Template Structure
Each onboarding template defines:
- Target department, location, employment type (full-time, contractor, intern), and worker type (office, remote, hybrid).
- An ordered list of template tasks with due offsets (e.g. -3 days before start date, 0 for start day, +7 days for security training).

## Rule-Based Template Resolution
When initializing a case, `OnboardingCaseService::resolveTemplateByRules` queries:
1. Department-specific template for the employee's department.
2. Fallback to default active corporate template if no department match exists.
3. Automatically pins the case to the template's latest published `HcmOnboardingTemplateVersion`.
