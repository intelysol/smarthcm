# Recruitment Data Model & Database Schema

## Normalized Table Schema
All tables are multi-tenant scoped with `tenant_id` foreign keys and UUID primary keys.

1. **`hcm_recruitment_sources`**: Sourcing channels (career portal, referral, agency, job boards).
2. **`hcm_recruitment_job_templates`**: Standard job definitions with requirements and competencies.
3. **`hcm_recruitment_requisitions`**: Requisition headers linked to Core HR positions and Workforce Plans.
4. **`hcm_recruitment_requisition_approvals`**: Multi-level workflow approvals.
5. **`hcm_recruitment_job_postings`**: Public listings with SEO slugs and publication status.
6. **`hcm_recruitment_candidates`**: Candidate records independent of employees.
7. **`hcm_recruitment_candidate_profiles`**: Resumes, bios, skills, and histories.
8. **`hcm_recruitment_candidate_consents`**: GDPR consent records with retention expiry.
9. **`hcm_recruitment_candidate_tags`**: Recruiter tags (`High Potential`, `Referral`).
10. **`hcm_recruitment_talent_pools` & `_candidates`**: Sourcing pools.
11. **`hcm_recruitment_application_stages`**: Configurable pipeline stages per tenant.
12. **`hcm_recruitment_applications`**: Requisition applications with status and screening results.
13. **`hcm_recruitment_application_activities`**: Immutable status transition and recruiter activity audit log.
14. **`hcm_recruitment_screenings`**: Structured screening checklist outcomes.
15. **`hcm_recruitment_interviews`**: Scheduled interview sessions.
16. **`hcm_recruitment_interview_participants`**: Panel members / interviewers.
17. **`hcm_recruitment_interview_evaluations`**: Individual interviewer scorecards and confidential notes.
18. **`hcm_recruitment_offers`**: Formal employment offers with status and financial figures.
19. **`hcm_recruitment_offer_versions`**: Version history (`v1`, `v2`) capturing compensation revisions.
20. **`hcm_recruitment_offer_approvals`**: Approval workflow records prior to sending offer.
21. **`hcm_recruitment_background_checks`**: Pre-employment verification checks.
22. **`hcm_recruitment_hiring_decisions`**: Final sanctioned hire record and Core HR employee ID linkage.
23. **`hcm_recruitment_referrals`**: Internal employee referral tracking and reward records.
24. **`hcm_recruitment_candidate_messages`**: Communication threads between recruiters and candidates.
