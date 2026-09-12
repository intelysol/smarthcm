# Cross-Domain Integrations

## Integration Map
Organizational Design and Job Architecture establish the standard workforce reference model consumed by all downstream modules:

| Consuming Module | Integration Mechanism | Data Consumed | Boundary Guarantee |
|---|---|---|---|
| **Core HR / Positions** | Foreign keys on `positions` | `job_id`, `job_profile_id`, `job_family_id` | Org Design defines classifications; Core HR owns seats and headcount. |
| **Recruitment / ATS** | Requisition mapping | Standard Job Profiles, skill & competency requirements | Requisitions reference profiles rather than free-form job strings. |
| **Compensation** | Grade & Band references | `job_grade_id`, evaluation suggested grade | Compensation owns actual salary structures; Org Design provides non-binding suggestions. |
| **Performance** | Review expectation linkages | Competency expectations & job responsibilities | Performance owns reviews and cycle outcomes. |
| **Career & Talent** | Career path linking | Career tracks, career levels, and progression steps | Career owns employee succession plans and aspirations. |
| **Learning** | Prerequisite mappings | Role-specific mandatory certifications and courses | Learning owns course enrollments and completions. |
| **Workforce Planning** | Demand planning dimensions | Job families, sub-families, and career levels | Workforce Planning owns supply/demand forecasts. |
