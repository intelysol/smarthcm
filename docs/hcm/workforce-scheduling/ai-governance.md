# Advisory AI Governance & Safety Boundaries

## Ethical AI Boundaries

The advisory AI scheduling assistant (`AdvisorySchedulingAiService`) is engineered with strict non-negotiable safety guardrails:

### Core Rules
1. **Strictly Advisory (`is_advisory_only: true`)**:
   - AI outputs are recommendations, never autonomous executions.
   - Every AI response explicitly sets `human_review_required: true`.
2. **Zero Adverse Employment Actions**:
   - AI must NEVER recommend terminating an employee, demoting an individual, reducing pay, or imposing disciplinary measures.
3. **Transparent Trade-offs & Confidence**:
   - Every recommendation provides clear operational rationale, an explicit confidence score (e.g. 0.92), and a documented trade-off statement (e.g., "May increase overtime cost").
4. **No Discriminatory Inferences**:
   - Algorithms and heuristics evaluate only operational factors (skills, availability, rest intervals, contractual hours). They never infer or utilize protected personal characteristics.
