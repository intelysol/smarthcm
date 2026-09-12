# Formal Absence Case Management

## Long-Term & Chronic Absence Governance
When employee absences exceed statutory or policy thresholds (e.g., Bradford Factor > 250 or > 20 days absent in a quarter), an `HcmAbsenceCase` is initiated:
- Links all associated `HcmAbsencePeriod` records.
- Records case status (`open`, `investigating`, `under_review`, `closed`).
- Tracks review dates, support interventions, and formal HR communications.
- Integrates directly with Epic 2.41 (HR Shared Services & Case Management).