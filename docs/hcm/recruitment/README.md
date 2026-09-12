# Flow HCM — Recruitment & Applicant Tracking System (ATS)

## Overview
Epic 2.26 delivers an enterprise-grade Recruitment and Applicant Tracking System (ATS) that handles the complete talent acquisition lifecycle:
from Workforce Planning approved positions and job requisitions, through candidate sourcing, applicant pipelines, panel interviews, evaluations, versioned offers, to formal hiring handoff into Core HR and Onboarding.

```text
WORKFORCE PLANNING
        │
        ▼
HIRING PLAN
        │
        ▼
JOB REQUISITION
        │
        ▼
REQUISITION APPROVAL
        │
        ▼
RECRUITMENT
        ├── Job Posting (Careers Portal)
        ├── Candidates & Profiles
        ├── Applications
        ├── Screening & Shortlisting
        ├── Panel Interviews & Scorecards
        └── Assessments
                │
                ▼
              OFFER (Versioned v1 -> v2)
                │
                ▼
         PRE-EMPLOYMENT CHECKS
                │
                ▼
             HIRING SANCTION
                │
         ┌──────┴──────┐
         ▼             ▼
     CORE HR       ONBOARDING
```

## Core Modules
1. **Job Requisitions**: Linked to Workforce Planning plans (`workforce_plan_id`, `position_plan_id`, `hiring_plan_id`) and Core HR positions with frozen position validation.
2. **Candidate Master & Profiles**: Independent candidate entities separate from employees, with talent pools, tagging, and duplicate candidate detection.
3. **Application Pipeline**: Multi-stage configurable pipelines with audit logging and rules-driven screening.
4. **Interviews & Panels**: Multi-interviewer scheduling, structured evaluations, and confidential scorecards.
5. **Offer Management**: Versioned offers (`v1` $\to$ `v2`), multi-step approval, and candidate acceptance.
6. **Pre-Employment Checks & Hiring**: Mandatory background check validation before Core HR employee provisioning and Onboarding kickoff.
7. **Recruitment Analytics**: Real-time funnel yield, Time-to-Hire, Cost-per-Hire, and recruiter KPI dashboards.
8. **AI Recruiter Assistant**: Explainable candidate matching with strict non-autonomous guardrails blocking automated rejection or ranking.
