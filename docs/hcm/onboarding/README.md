# Flow HCM — Onboarding & Preboarding Platform

## Overview
Epic 2.27 delivers an enterprise-grade **Onboarding, Preboarding, Employee Lifecycle Tasks & New Hire Experience** orchestration platform for Flow HCM.

It coordinates the complete transition of new hires from offer acceptance in Recruitment to active, productive employment in Core HR:

```text
RECRUITMENT (Offer Accepted)
    ↓
CORE HR (Employee Created)
    ↓
ONBOARDING CASE INITIALIZED
    ├── Preboarding Tasks
    ├── Document Collection & Verification
    ├── Digital Joining Forms (Direct Deposit & Emergency Contacts)
    ├── Policy Acknowledgements (Handbook, Security)
    ├── IT Access & Equipment Provisioning
    ├── Orientation & Buddy Assignment
    └── Mandatory Compliance Training
            ↓
      FIRST DAY READY
            ↓
      PROBATION (30-90 Days)
            ↓
     ONBOARDING COMPLETED
            ↓
    EMPLOYEE LIFECYCLE (Transfers, Promotions)
```

## Architectural Tenet
> **Onboarding is an orchestration layer.**
> It does NOT own or duplicate the master data for Employee, Payroll, Benefits, Learning, Documents, or Assets. It triggers and tracks milestones across those authoritative systems of record.
