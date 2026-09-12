# Job Architecture Framework

## Enterprise Taxonomy
The Job Architecture defines the structured blueprint for classifying work across the enterprise:

```mermaid
graph TD
    JF[Job Family: e.g. Technology] --> JSF[Job Sub-Family: e.g. Software Engineering]
    JSF --> JOB[Job Definition: e.g. Software Engineer]
    
    CT[Career Track: e.g. Individual Contributor] --> CL[Career Level: e.g. L3 - Senior]
    JL[Job Level: e.g. Senior Professional] --> JP[Job Profile: Standardized Specifications]
    
    JOB --> JP
    CL --> JP
    JL --> JP
    
    JP --> POS1[Position #ENG-101 Karachi]
    JP --> POS2[Position #ENG-102 Dubai]
    JP --> POS3[Position #ENG-103 London]
```

---

## Job vs. Position Separation
* **Job (`organization_job_definitions`, `job_profiles`)**: The abstract, reusable specification of work duties, required qualifications, skills, and competencies.
* **Position (`positions`)**: The specific budgeted organizational seat within a department, location, and reporting line. A job can be instantiated across many positions.
