# Flow HCM — Learning Management, Training & Employee Development (Epic 2.25)

## Executive Overview
The **Flow HCM Learning Management System (LMS)** provides an enterprise-grade platform for continuous workforce development, mandatory compliance education, certifications, and career-aligned individual development plans.

### Bounded Context Principle
Learning is an independent bounded context adhering to the principle:
> **Learning records what an employee learned, completed, and achieved. Skills determine capability. Performance evaluates performance. Career manages career direction. Talent manages talent decisions. Workforce Planning manages future workforce requirements. Analytics connects the information without becoming the system of record.**

---

## Key Capabilities

1. **Comprehensive Learning Catalog**: Courses, training programs, structured learning paths, workshops, webinars, assessments, and external certifications.
2. **Course Versioning**: Historical records preserve the exact course version completed; modifying course materials never alters completed historical transcripts.
3. **Multi-Delivery Modalities**: Online (self-paced e-learning, SCORM, video, PDF), classroom (physical venues), virtual meetings, and blended learning.
4. **Mandatory & Compliance Education**: Automated requirement assignments by company, department, job role, position, or location with compliance rate tracking.
5. **Assessments & Question Bank**: Versioned question banks, multiple question types, configurable attempts, and server-controlled scoring.
6. **Certificates & Public Verification**: Automated certificate generation upon completion, unique verification codes, and public verification endpoint (`/verify/certificate/{code}`).
7. **Certification & Expiry Monitoring**: Professional license and external certification tracking with automated 90/60/30-day expiration alerts.
8. **Individual Development Plans (IDP)**: Career and competency-driven development goals with concrete activities (courses, mentoring, stretch assignments, coaching).
9. **Budget & Cost Tracking**: Department and cost-center training budgets with Total Cost of Training using exact decimal/Money arithmetic.
10. **Explainable AI Recommendations**: Non-autonomous AI guidance matching skill gaps and career goals with strict safety guardrails.
