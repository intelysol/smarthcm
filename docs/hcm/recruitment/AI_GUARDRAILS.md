# AI Recruitment Guardrails & Ethical Boundaries

## Non-Autonomous Design Principle
AI in Flow HCM ATS serves purely as an **advisory tool** (`is_advisory => true`).

## Strict Prohibitions
1. **No Autonomous Rejection**: AI is technically blocked from rejecting or disqualifying applicants. Disciplinary or adverse queries return a guardrail block.
2. **No Autonomous Ranking**: Candidates cannot be sorted into definitive hiring ranks by black-box algorithms.
3. **No Demographic Profiling**: AI does not infer or utilize protected attributes (age, race, gender, religion).
4. **Mandatory Explainability**: Every match score reports explicit contributing factors:
   - `skills_match` (list of overlapping skills)
   - `experience_match` (years of domain practice)
   - `certification_match` (verified credentials)
