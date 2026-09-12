# Applications & Candidate Screening

## Application Model
An application (`HcmRecruitmentApplication`) links a candidate to a specific job requisition.
A candidate can only have one active application per requisition at any time.

## Statuses
- `new`
- `screening`
- `shortlisted`
- `interview`
- `assessment`
- `offer`
- `hired`
- `rejected`
- `withdrawn`
- `on_hold`

## Screening Engine
Screening validates:
- Skills Match
- Experience Match
- Education Match
- Salary Expectations Match

Outcomes update application status to either `shortlisted` or `rejected` with documented feedback.
