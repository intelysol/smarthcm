# Recruitment Testing Strategy

## Test Suites
Recruitment features are verified using 10 comprehensive test suites in `tests/Feature/Recruitment/`:
1. `JobRequisitionLifecycleAndPositionValidationTest.php`: Tests requisition drafting, submission, approval, and frozen position prevention.
2. `CandidateManagementAndDuplicateDetectionTest.php`: Tests candidate profiles, tags, and fuzzy email/phone duplicate detection.
3. `ApplicationPipelineAndScreeningTest.php`: Tests application submission, stages, and structured screening validation.
4. `InterviewSchedulingAndEvaluationScorecardTest.php`: Tests panel scheduling and confidential scorecard computation.
5. `OfferManagementAndVersioningTest.php`: Tests offer drafting, version bumping (`v1` to `v2`), approval, and acceptance.
6. `HiringHandoffToCoreHrAndOnboardingTest.php`: Tests background checks validation, hire decision, and Core HR payload handoff.
7. `RecruitmentAnalyticsAndFunnelConversionTest.php`: Tests funnel yield math, conversion percentages, and Time-to-Hire calculation.
8. `RecruitmentAiMatchingAndGuardrailsTest.php`: Tests explainable match factors and safety blocks against autonomous adverse decisions.
9. `RecruitmentSecurityAndConfidentialityTest.php`: Tests multi-tenant isolation, confidential requisition gating, and scorecard sanitization.
10. `PublicCareersPortalAndApplicationTest.php`: Tests public careers browsing and candidate self-service application submission.
