# Learning Assessments & Question Bank

## Assessment Architecture

- **Question Types**:
  - Multiple Choice (Single Select)
  - Multiple Select
  - True / False
  - Short Answer / Essay
  - Practical Assignment Upload
- **Server-Controlled Scoring**:
  - The client UI never submits grades or answers with answer keys.
  - Answers are evaluated server-side against the versioned question bank.
  - Passing thresholds (`passing_score_percentage`, e.g. 80%) are strictly validated.
- **Configurable Attempts**:
  - Maximum attempt restrictions (`max_attempts`).
  - Cooldown periods between retries.
  - Preservation of full attempt audit logs.
