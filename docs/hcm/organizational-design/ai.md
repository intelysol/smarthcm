# Advisory AI Engine & Governance Guardrails

## Non-Autonomous Design Principles
All AI-driven capabilities within the Organizational Design domain strictly enforce deterministic, advisory guardrails:

1. **Explicit Advisory Flag**: All AI suggestions return `is_advisory_only: true`.
2. **Zero Autonomous Write Authority**: AI never creates, modifies, or retires job profiles, never alters position assignments, never mutates organizational units, and never determines compensation.
3. **Human-in-the-Loop Governance**: AI drafts and standardization recommendations must be formally reviewed and approved by authorized HR architects.

---

## Capabilities Provided

### 1. Job Profile Drafting & Suggestions
When an HR architect defines a new role title (e.g. *Cloud Security Architect*), the AI suggests:
* Role Summary
* Key Core Responsibilities
* Technical & Domain Skills (mapped to common taxonomy)
* Behavioral Competencies

### 2. Title Standardization & Duplicate Anomaly Detection
Identifies common variations in job titles across departments:
* Detects variations like *"Sr Software Engineer"*, *"Sr. Software Dev"*, and *"Senior Software Engineer"*.
* Recommends a single enterprise canonical title.
* Flags potential standardization candidates to reduce workforce fragmentation.
