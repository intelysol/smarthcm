# Workforce Optimization & Decision Intelligence

Enterprise prescriptive intelligence domain transforming capacity, productivity, cost, and skills signals into actionable workforce optimization recommendations.

## Overview
The Workforce Optimization engine continuously evaluates operational bottlenecks and delivers multi-objective recommendations spanning six core action pathways:
1. **Hire** (Full-time / part-time permanent requisitions)
2. **Reskill / Upskill** (Learning development assignments)
3. **Redeploy / Internal Mobility** (Cross-department transfers)
4. **Contractor / Contingent** (Flexible labor buffer)
5. **Shift Rebalancing** (Schedule realignment across shifts)
6. **Schedule Optimization / Work Redesign** (Capacity leveling)

## Architecture
- **Domain Directory:** `app/Domains/WorkforceOptimization/`
- **Database Tables:** 14 dedicated tables prefixed with `hcm_workforce_optimization_*`
- **Solver Strategy:** Multi-objective weighted Pareto scoring (`OptimizationSolverService`)
- **Guardrails:** Non-punitive, zero employee surveillance, advisory-only AI service (`AdvisoryWorkforceOptimizationAiService`)
- **Closed Loop:** Realized outcome tracking comparing baseline values with post-action values to measure actual financial and operational variance (`OptimizationOutcomeService`).

## Artisan Commands
- `php artisan hcm:optimization-run {--tenant=}`: Run optimization pipeline
- `php artisan hcm:detect-opportunities {--tenant=}`: Discover capacity and overtime opportunities
- `php artisan hcm:measure-outcomes {--tenant=}`: Track realized post-implementation metrics
