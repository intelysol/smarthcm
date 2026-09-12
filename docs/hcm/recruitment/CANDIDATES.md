# Candidate Management, Talent Pools & Duplicate Detection

## Candidate Master
Candidates exist independently of employees and can apply to multiple requisitions.
Each candidate record tracks:
- `candidate_number`
- Full name, normalized email, phone
- Source channel (`HcmRecruitmentSource`)
- Consent status & GDPR tracking

## Duplicate Detection
`CandidateService::detectDuplicates` checks for existing candidate records within the tenant using:
- Exact email match (case-insensitive)
- Normalized phone number pattern matching (minimum 7 numerical digits)

## Talent Pools
Recruiters can group candidates across job families into pools (`HcmRecruitmentTalentPool`) for future outreach without creating duplicate records.
