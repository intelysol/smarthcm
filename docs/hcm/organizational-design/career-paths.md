# Career Paths & Progression Routes

## Multi-Track Progression
Employees can visualize dual-ladder career progression:

```mermaid
graph TD
    SWE1[Software Engineer I - L1] --> SWE2[Software Engineer II - L2]
    SWE2 --> SR[Senior Software Engineer - L3]
    
    SR -->|Technical Track| STAFF[Staff Engineer - L4]
    STAFF --> PRINCIPAL[Principal Engineer - L5]
    
    SR -->|Management Track| MGR[Engineering Manager - M1]
    MGR --> SR_MGR[Senior Engineering Manager - M2]
    SR_MGR --> DIR[Director of Engineering - M3]
```

---

## Progression Step Criteria
Each step in a career path (`CareerPathStep`) references:
* Target Job Profile & Level.
* Required technical skills (`required_skills`).
* Target behavioral & leadership competencies (`required_competencies`).
* Minimum experience years (`minimum_experience_years`).
* Performance rating benchmark (`performance_min_rating`).
