# Schedule Optimization Engine

## Optimization Philosophy

The schedule optimization service (`ScheduleOptimizationService`) operates under strict human-in-the-loop governance:
1. **Never Auto-Publishes**: The optimization run generates proposed assignments stored in `hcm_schedule_optimization_runs`.
2. **Deterministic Rules**: The algorithm evaluates candidates through `ScheduleEligibilityService` before scoring.
3. **Multi-Objective Scoring**:
   - Baseline Eligibility: 50 points
   - Preferred Shift Match: +20 points
   - Avoided Shift Penalty: -30 points
   - Workload Balance: Up to +50 points for workers with fewer assigned weekly hours.

## Review and Application Workflow
```text
Run Optimizer (POST /api/v1/hcm/scheduling/periods/{id}/optimize)
       ↓
Proposed Assignments Stored in hcm_schedule_optimization_runs
       ↓
Manager Reviews Metrics (Coverage %, Proposed Assignments, Trade-offs)
       ↓
Manager Approves & Applies (POST /api/v1/hcm/scheduling/optimization-runs/{id}/apply)
       ↓
Live Roster Assignments Created (Status: scheduled, is_published: false)
```
