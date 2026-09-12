# HCM Organizational Design & Job Architecture Governance

## Executive Overview
The **Organizational Design & Job Architecture** module provides SmartHCM with a centralized reference model and governance framework for enterprise workforce structures. It decouples the abstract classification of work (Job Families, Sub-Families, Jobs, Levels, Tracks, and Job Profiles) and organizational modeling (Scenarios, Reorganizations, Tree Planning) from transactional Core HR records and Position Management seats.

---

## Key Core Pillars

1. **Job Architecture Hierarchy**
   - Structural taxonomy: `Job Family` → `Job Sub-Family` → `Job` → `Job Level` & `Career Track` → `Career Level`.
   - Clear distinction: *Job Architecture defines what work is*; *Position Management defines which seats exist*; *Core HR defines who occupies those seats*.

2. **Job Profiles & Versioned Specifications**
   - Standardized job profiles containing responsibilities, education, experience, travel, and remote work eligibility.
   - Skill requirements mapping to authoritative `CareerSkill` models.
   - Competency expectations mapping to authoritative `Competency` models.
   - Immutable versioning snapshots ensuring historical compliance and audit defensibility.

3. **Multi-Factor Job Evaluation Engine**
   - Standardized point-factor scoring across Knowledge, Problem Solving, Accountability, Impact, and Leadership.
   - Non-binding benchmark recommendations to `JobGrade` without directly mutating compensation ranges.

4. **Career Frameworks & Progression Routes**
   - Career tracks (`individual_contributor`, `management`, `technical_specialist`, `executive`).
   - Integrated career path steps with minimum experience and competency requirements.

5. **Non-Destructive Organization Scenarios**
   - Visual organizational design and restructuring scenarios (Current State vs. Future State vs. Alternative Scenarios).
   - Cloning of live Core HR organizational hierarchies into isolated scenario planning models.
   - Structure comparison highlighting added, merged, split, and removed organizational nodes without mutating Core HR.

6. **Cross-Domain Architecture Impact Analysis**
   - Pre-activation impact queries determining affected positions, employees, open requisitions, compensation bands, and learning requirements.

7. **Architecture Health & Governance**
   - Automated anomaly detection for unmapped jobs, duplicate title variations (e.g. "Sr Software Engineer" vs "Senior Software Engineer"), and retired job references.
   - Advisory AI suggestions for profile drafting, skills recommendation, and title standardization.

---

## Documentation Quick Links
* [Architecture & Domain Authority](architecture.md)
* [Organizational Design Scenarios](organization-design.md)
* [Job Architecture Framework](job-architecture.md)
* [Job Families & Sub-Families](job-families.md)
* [Job Profiles & Governance](job-profiles.md)
* [Job Levels & Career Tracks](job-levels.md)
* [Career Framework](career-framework.md)
* [Career Paths](career-paths.md)
* [Job Evaluation Engine](job-evaluation.md)
* [Impact Analysis](impact-analysis.md)
* [Architecture Governance & Health](governance.md)
* [Cross-Domain Integrations](integrations.md)
* [REST API Reference](api.md)
* [Security & Access Control](security.md)
* [Testing & Verification](testing.md)
* [Advisory AI Engine](ai.md)
