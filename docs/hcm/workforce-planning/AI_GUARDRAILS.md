# AI Guardrails & Non-Autonomous Standards

## Permitted AI Planning Operations
- Summarizing complex multi-period workforce plans.
- Generating explanatory narratives for labor cost and headcount variances.
- Comparing scenarios and detailing trade-offs (e.g. growth vs cost reduction).
- Recommending aggregate workforce actions (upskilling, internal mobility, contractor balance).

## Prohibited Operations (Strict Platform Violations)
- Profiling or selecting individual employees for termination or redundancy.
- Generating individual employee loyalty or flight risk scores.
- Autonomously creating, modifying, approving, or locking plans.
- Autonomously creating employee terminations or position eliminations.

All AI-generated insights carry explicit `is_advisory: true` flags and guardrail audit metadata.
