# Onboarding Testing Strategy

## Test Suite Coverage
Onboarding capabilities are verified through 11 comprehensive feature test suites located in `tests/Feature/Onboarding/`:
1. `OnboardingCaseLifecycleAndTemplateAssignmentTest.php`: Tests case initialization, template versioning, task generation, and progress recalculation.
2. `OnboardingTaskEngineAndDependencyTest.php`: Tests prerequisite dependency enforcement and cascading unblocking.
3. `OnboardingDocumentVerificationTest.php`: Tests document submission, reviewer verification, and rejection handling.
4. `OnboardingPolicyAndDigitalFormTest.php`: Tests versioned policy acknowledgement and digital form submissions.
5. `OnboardingProvisioningAndBuddyTest.php`: Tests IT access/equipment provisioning requests and buddy assignment.
6. `OnboardingProbationTrackingAndExtensionTest.php`: Tests probation lifecycle, extensions, and review sign-off.
7. `OnboardingEmployeePortalAndSecurityTest.php`: Tests employee self-service scoping and sensitive banking/tax masking.
8. `OnboardingAnalyticsAndReadinessKpisTest.php`: Tests first-day readiness %, completion rates, and duration metrics.
9. `OnboardingAiAssistanceAndGuardrailsTest.php`: Tests welcome message drafting, readiness summaries, and AI safety guardrails.
10. `OnboardingCrossDomainHandoffTest.php`: Tests the handoff from Recruitment/Core HR into active Onboarding.
11. `OnboardingSeedersTest.php`: Tests permission and default template seeders execution.
