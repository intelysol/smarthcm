# Learning Architecture & System Boundaries

## Architectural Topology

```text
EMPLOYEE / MANAGER
   │
   ▼
DEVELOPMENT NEED
   ├── Skill Gap (Skills Domain)
   ├── Competency Gap (Performance Domain)
   ├── Career Goal (Career Domain)
   └── Mandatory Requirement (Compliance Domain)
   │
   ▼
LEARNING RECOMMENDATION (AI / Rules)
   │
   ▼
CATALOG ITEM (Course / Program / Learning Path)
   │
   ▼
ENROLLMENT & NOMINATION (Workflow Approvals)
   │
   ▼
LEARNING ACTIVITY (Modules / Content / Sessions)
   │
   ▼
ASSESSMENT & QUIZ (Server-Controlled Scoring)
   │
   ▼
COMPLETION & CERTIFICATE (Documents)
   │
   ▼
SKILL EVIDENCE / CERTIFICATION RENEWAL
   │
   ▼
TALENT & WORKFORCE PLANNING
```

## System Ownership Invariants

1. **Learning Owns**:
   - Course, program, and path catalog
   - Enrollments, waitlists, and nominations
   - Progress tracking and learning activity events
   - Assessment questions, attempts, and scores
   - Completion records, certificates, and training transcripts
   - Individual Development Plans (IDP) and activities
   - Training attendance, session scheduling, and training budgets
2. **Learning Does NOT Own**:
   - Employee master records (owned by Core HR)
   - Skills master & verified levels (owned by Skills domain)
   - Competency definitions (owned by Performance domain)
   - Career aspirations and succession readiness (owned by Career & Succession)
   - Time & Attendance (owned by Time & Attendance; training attendance is kept distinct)
   - General ledger accounting (owned by Finance)
