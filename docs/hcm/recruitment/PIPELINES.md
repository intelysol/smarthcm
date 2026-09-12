# Recruitment Pipelines & Stage Transitions

## Configurable Stages
Each tenant defines an ordered sequence of application stages (`HcmRecruitmentApplicationStage`):
1. New Application (`NEW`)
2. Resume Screening (`SCREENING`)
3. Shortlisted (`SHORTLISTED`)
4. Interviewing (`INTERVIEW`)
5. Technical Assessment (`ASSESSMENT`)
6. Offer Extended (`OFFER`)
7. Hired (`HIRED`)

## Activity Auditing
Every stage change or status modification creates an immutable audit record in `hcm_recruitment_application_activities` recording:
- `actor_id`
- `activity_type`
- `from_state`
- `to_state`
- `notes`
