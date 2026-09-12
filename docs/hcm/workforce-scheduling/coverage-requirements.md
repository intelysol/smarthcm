# Coverage Requirements & Ingestion

## Overview

Workforce coverage requirements bridge high-level capacity planning (Epic 2.45) with tactical shift assignments.
Stored in `hcm_schedule_coverage_requirements`, coverage lines define staffing needs by:
- `requirement_date`: Specific day.
- `shift_definition_id`: Target shift (morning, evening, night).
- `required_headcount`: Number of workers needed.
- `department_id` / `location_id`: Organizational context.
- `required_skill_id` & `min_proficiency_level`: Skill requirements (1-5).
- `position_id`: Specific position requirements.

## Ingestion Pipeline from Capacity (Epic 2.45)

```text
Business Demand (Volume)
       ↓
Epic 2.45 Capacity Calculations (Workload × Effort → Required FTE)
       ↓
Coverage Ingestion Service (CoverageCalculationService::ingestRequirements)
       ↓
hcm_schedule_coverage_requirements
       ↓
Heatmap Matrix & Schedule Optimization
```

## Coverage Heatmap Matrix
Coverage calculation outputs:
- **Required**: Target headcount.
- **Scheduled**: Assigned workers with status `scheduled`.
- **Gap**: `Scheduled - Required`.
- **Coverage %**: `Scheduled / Required × 100`.
- **Status Indicator**:
  - `under_covered` (Gap < 0)
  - `optimal` (Gap = 0)
  - `over_covered` (Gap > 0)
