# Recruitment Security & Access Control

## Multi-Tenant Isolation
All recruitment tables enforce strict tenant filtering (`where('tenant_id', $tenantId)`). Recruiter actions cannot view or modify cross-tenant records.

## Confidential Requisition Access
Requisitions marked `is_confidential = true` are restricted:
- Only assigned recruiters, designated approvers, or platform administrators can view or access applicant records.

## Interview Scorecard Privacy
Candidate accounts cannot view interviewer internal evaluations or confidential feedback notes.
`RecruitmentSecurityService::sanitizeScorecardForCandidate` strips confidential commentary.
