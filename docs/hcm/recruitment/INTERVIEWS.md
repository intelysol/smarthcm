# Interview Management, Panels & Scorecards

## Interview Types
Supported interview types via `InterviewType`:
- `phone`: Recruiter introductory screening
- `video`: Remote virtual screening
- `technical`: Coding, architectural, or technical depth interview
- `panel`: Multi-interviewer consensus panel
- `manager`: Hiring manager fit interview
- `hr`: Behavioral & compensation alignment
- `final`: Executive or bar-raiser interview

## Interview Panels
Each interview supports multiple participants in `hcm_recruitment_interview_participants`.
Each panelist submits an independent, confidential scorecard (`HcmRecruitmentInterviewEvaluation`):
- `technical_rating` (0.0 - 5.0)
- `communication_rating` (0.0 - 5.0)
- `problem_solving_rating` (0.0 - 5.0)
- `overall_score` (computed average)
- `recommendation` (`strong_yes`, `yes`, `neutral`, `no`, `strong_no`)
- `confidential_notes` (never disclosed to candidate)
