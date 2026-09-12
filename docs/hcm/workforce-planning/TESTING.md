# Workforce Planning Testing Strategy

## Test Suite Coverage

Feature test suites under `tests/Feature/WorkforcePlanning/`:
1. `WorkforcePlanLifecycleAndLockingTest.php`: Plan creation, monthly period initialization, approval workflow, locking, and versioning.
2. `HeadcountPlanningDeterministicCalculationTest.php`: Mathematical balance validation ($\text{Closing} = \text{Opening} + \text{Hires} + \text{Transfers In} - \text{Exits} - \text{Transfers Out}$).
3. `PositionPlanningAndBudgetTest.php`: Position lifecycle (`planned`, `budgeted`, `frozen`, `eliminated`), vacancies, and Total Employment Cost decimal arithmetic.
4. `WorkforceDemandSupplyAndGapTest.php`: Driver-based required FTE demand, organic supply projection, and workforce gap analysis.
5. `LaborCostPlanningAndVarianceTest.php`: Labor cost summation, budget variance, and zero floating-point drift.
6. `HiringPlanAndReplacementHiringTest.php`: Position-linked hiring requirement prioritization, replacement reasons, and recruitment handoff payload.
7. `WorkforceScenarioModelingAndComparisonTest.php`: Growth, Cost Reduction, and Hiring Freeze scenario simulations and side-by-side comparison matrix.
8. `ActualVsPlanAnalyticsIntegrationTest.php`: Actual (Core HR) vs Planned vs Budgeted vs Forecast matrix calculation.
9. `WorkforcePlanningSecurityAndScopeTest.php`: Multi-tenant boundary checks, department manager scope restrictions, and sensitive budget access control.
10. `WorkforceAiPlanningAssistantTest.php`: AI summary narratives, scenario explanations, and enforcement of adverse action guardrails.
