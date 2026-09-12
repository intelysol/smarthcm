# REST API Reference & Endpoints

## Base URL: `/api/v1/org-design`

### 1. Job Architecture
* `GET /job-architecture/tree`
  * Retrieves complete job architecture tree (families, sub-families, career tracks, levels).
* `POST /job-architecture/families`
  * Create a Job Family. Body: `{ code, name, description }`.
* `POST /job-architecture/families/{familyId}/sub-families`
  * Create a Job Sub-Family. Body: `{ code, name, description }`.
* `POST /job-architecture/career-tracks`
  * Create a Career Track. Body: `{ code, name, track_type, description }`.
* `POST /job-architecture/job-levels`
  * Create a Job Level. Body: `{ code, name, numerical_level, description }`.

---

### 2. Job Profiles & Impact
* `GET /job-profiles`
  * Paginated list of job profiles with filters.
* `POST /job-profiles`
  * Create a Job Profile with initial version snapshot.
* `GET /job-profiles/{id}`
  * Retrieve profile detail, required skills, competencies, and version history.
* `PUT /job-profiles/{id}`
  * Update profile and create next version snapshot.
* `GET /job-profiles/{id}/impact`
  * Run pre-activation impact analysis across positions, employees, requisitions, and compensation.

---

### 3. Job Evaluation
* `POST /job-profiles/{profileId}/evaluations`
  * Submit multi-factor point scoring: `{ knowledge_score, problem_solving_score, accountability_score, impact_score, leadership_score, notes }`.

---

### 4. Organization Scenarios
* `GET /scenarios`
  * List organizational design scenarios.
* `POST /scenarios`
  * Create a planning scenario.
* `POST /scenarios/{scenarioId}/clone-live`
  * Non-destructively clone live Core HR hierarchy into scenario nodes.
* `POST /scenarios/{scenarioId}/nodes`
  * Add a planned node to the scenario.
* `GET /scenarios/{scenarioId}/compare`
  * Run delta diff comparing scenario against live state.

---

### 5. Governance & Advisory AI
* `GET /governance/health-scan`
  * Trigger immediate architecture health scan for anomalies.
* `POST /governance/ai/draft-profile`
  * Advisory AI suggestions for responsibilities and skills: `{ title, job_family }`.
* `POST /governance/ai/standardize-title`
  * Advisory AI title variation normalization check: `{ title }`.
