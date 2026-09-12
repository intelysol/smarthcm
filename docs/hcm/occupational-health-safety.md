# Epic 2.34 — HCM Employee Health, Occupational Safety, Medical Fitness, Workplace Health & Safety Compliance

## Architectural Overview & Non-Clinical Scope

Epic 2.34 implements an enterprise-grade occupational health and workplace safety architecture for multi-tenant, multi-jurisdiction enterprise HCM.

### Strict Non-Clinical Boundary
1. **Administrative & Compliance Layer**: This system is **not** an Electronic Medical Record (EMR) or hospital diagnostic tool.
2. **Clearance & Functional Capability**: It records **administrative fitness determinations** (`fit`, `fit_with_restrictions`, `temporarily_unfit`, `permanently_unfit`), operational accommodations, statutory safety incident reports, and occupational exposure metrics.
3. **Medical Privacy (Minimum-Necessary Standard)**:
   - Managers and general HR personnel **only see operational restrictions** (e.g. "Cannot lift above 25 lbs", "No rotating night shifts").
   - Detailed physician notes, clinical diagnoses, and medical rationales are strictly restricted to designated Medical Officers (`hcm.health.view_clinical_data` permission).

---

## Domain Architecture & Database Model

The domain operates under `App\Domains\HealthSafety` with 14 normalized tables:
- `hcm_health_requirement_types` & `hcm_health_requirements`: Surveillance and exam policy rules.
- `hcm_employee_health_requirements`: Individual employee tracking and status lifecycles.
- `hcm_medical_providers`: Accredited clinics and occupational medicine examiners.
- `hcm_medical_assessments`: Scheduled exams, clinical evaluations, and outcomes.
- `hcm_medical_fitness_records`: Official fitness-for-duty certificates and validity periods.
- `hcm_medical_restrictions`: Operational functional limitations and accommodation needs.
- `hcm_return_to_work_cases`: Phased RTW progression plans following injury/illness.
- `hcm_workplace_accommodations`: Ergonomic aids, assistive technologies, and schedule adjustments.
- `hcm_safety_incidents`: OSHA Form 300 recordables, near-misses, injuries, and lost-time logs.
- `hcm_safety_incident_witnesses`: Witness statements and interview records.
- `hcm_safety_incident_investigations`: 5-Why, Fishbone root cause and contributing factor analysis.
- `hcm_safety_incident_actions`: CAPA tracking with hierarchy of controls and effectiveness verification.
- `hcm_workplace_exposures`: Industrial hygiene monitoring (noise, chemicals, radiation).

---

## EHS Safety Metrics & Regulatory Compliance

1. **OSHA TRIR & LTIR Formulations**:
   - Total Recordable Incident Rate: `(Recordable Incidents * 200,000) / Total Hours Worked`
   - Lost Time Incident Rate: `(Lost Time Incidents * 200,000) / Total Hours Worked`
   - Severity Rate: `(Lost Work Days * 200,000) / Total Hours Worked`
2. **OSHA Form 300 / 300A Log Generator**:
   - Compiles annual establishment summary with breakdown of lost days, restricted work days, and classification.

---

## AI Health & Safety Advisor

- **Advisory Only Mode**: All AI suggestions provide `is_advisory => true` with statutory disclaimers.
- Assisting in incident severity triaging, OSHA recordability indication, and recommending hierarchy of controls.
