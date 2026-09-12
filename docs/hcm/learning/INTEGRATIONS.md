# Cross-Domain Integrations

## Upstream & Downstream Integration Points

1. **Skills Domain (`App\Domains\Skill`)**:
   - Learning supplies verifiable course completion evidence.
   - Skills domain evaluates evidence and updates verified employee skill ratings according to governance rules.
2. **Career & Succession (`App\Domains\Career`, `App\Domains\Succession`)**:
   - Learning paths align with target career roles.
   - Completion data informs succession readiness benchmarks.
3. **Performance Management (`App\Domains\Performance`)**:
   - Performance development objectives link to Individual Development Plans (IDP).
4. **Workforce Planning (`App\Domains\WorkforcePlanning`)**:
   - Upskilling and internal reskilling programs mitigate projected organizational workforce skill gaps.
5. **Documents Platform (`App\Domains\Document`)**:
   - Stores uploaded certificate PDFs, transcripts, and verifiable external evidence.
6. **Notifications (`App\Domains\Notification`)**:
   - Alerts learners on overdue mandatory compliance, expiring credentials, and upcoming classroom sessions.
