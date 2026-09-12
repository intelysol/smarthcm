# Learning Testing Strategy

## Test Suite Coverage

### Unit & Feature Tests (`tests/Feature/Learning/`, `tests/Unit/Learning/`)
1. **Course Management & Versioning**: Version creation, modules, syllabus structure, immutability of historical versions.
2. **Enrollment & Workflow**: Self-enrollment, manager nomination, waitlists, prerequisites.
3. **Assessments & Scoring**: Randomized questions, attempt limit enforcement, server-side score calculation.
4. **Certificates & Verification**: Hash generation, sanitized public verification response, expired/revoked certificate handling.
5. **Individual Development Plans (IDP)**: Goal setting, activity lifecycle (course, mentoring, coaching, stretch assignments).
6. **External Learning**: Off-platform learning submissions, document attachment, admin review.
7. **Compliance & Requirements**: Mandatory assignment by company/dept/role, overdue tracking.
8. **AI Recommendations**: Non-autonomous guardrail validation, blocking of disciplinary queries.
9. **Budget & Cost Precision**: Decimal/Money arithmetic with zero floating-point approximation error.
10. **Security & Scope**: Multi-tenant isolation, manager hierarchy scope, document authorization.
