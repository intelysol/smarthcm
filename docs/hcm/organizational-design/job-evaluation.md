# Multi-Factor Job Evaluation Engine

## Evaluation Methodology
The **Job Evaluation Engine** utilizes a point-factor methodology assessing job profiles across five standardized dimensions:

| Dimension | Scope & Description | Score Range |
|---|---|---|
| **Knowledge & Technical Expertise** | Breadth and depth of specialized skills, education, and domain knowledge required. | 0 - 250 pts |
| **Problem Solving & Complexity** | Novelty of challenges, analytical difficulty, and conceptual reasoning required. | 0 - 250 pts |
| **Accountability & Freedom to Act** | Degree of supervision, latitude in decision making, and budgetary accountability. | 0 - 200 pts |
| **Organizational Impact** | Directness and magnitude of the role's influence on operational or business outcomes. | 0 - 200 pts |
| **Leadership & Influence** | Team leadership, cross-functional persuasion, and strategic enterprise direction. | 0 - 100 pts |

---

## Benchmark Recommendation vs. Compensation Authority
* Evaluator scoring calculates a combined `total_points` score.
* The system matches the score against enterprise grade boundaries to produce a non-binding `suggested_job_grade_id`.
* **Guardrail**: Job evaluation recommendations never directly alter compensation bands, employee base pay, or salary ranges.
