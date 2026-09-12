# Onboarding Task Engine & Dependency Orchestration

## Task Lifecycle
Tasks transition through:
`Pending -> In Progress -> Blocked -> Completed` (or `Skipped` / `Cancelled`).

## Prerequisite Dependencies
Tasks can declare prerequisites via `hcm_onboarding_task_dependencies`.
- If a prerequisite is incomplete, the dependent task cannot be completed (throws a `ValidationException`).
- When all prerequisite tasks are satisfied, dependent tasks transition from `blocked` to `pending`.

## Progress Recalculation
Completing any required task automatically recalculates `completion_percentage` on the parent case:
$$\text{Progress \%} = \frac{\text{Completed Required Tasks}}{\text{Total Required Tasks}} \times 100$$
When 100% is reached, the case status transitions to `completed`.
