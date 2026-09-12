# Course Management & Versioning

## Course Structure Hierarchy

```text
Course (Header)
  ├── Version 1 (Published / Archived)
  ├── Version 2 (Active)
        ├── Module 1: Introduction
        │     ├── Lesson 1: Welcome & Objectives (Video)
        │     └── Lesson 2: Policy Guide (PDF Document)
        ├── Module 2: Core Concepts
        │     ├── Lesson 1: Architecture Principles (SCORM / HTML)
        │     └── Lesson 2: Case Studies (External URL)
        └── Module 3: Knowledge Assessment
              └── Final Exam (Quiz / Assessment)
```

## Immutability & Versioning Rules

- **Completed Transcripts are Immutable**: When a course syllabus or content is updated, a new version (`v2`) is created.
- Employees who completed `v1` retain an immutable completion record tied to `v1`. Their certificate and historical score remain untouched.
- Active enrollees can be migrated or allowed to complete their existing enrolled version based on tenant policy.

## Delivery Types
- `online`: Self-paced asynchronous digital learning.
- `classroom`: In-person instructor-led session at a physical facility.
- `virtual`: Real-time synchronous video conference training.
- `blended`: Combination of online self-study and interactive sessions.
