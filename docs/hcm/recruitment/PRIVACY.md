# Candidate Privacy, GDPR Consent & Data Retention

## GDPR Consent Lifecycle
Candidates grant explicit consent recorded in `hcm_recruitment_candidate_consents`:
- Versioned consent (`v1.0`)
- Purpose specification (`recruitment_processing`, `talent_pool`)
- Retention expiry dates (default 2 years from grant)

## Automated Retention & Legal Hold
`ProcessCandidateRetentionJob`:
1. Identifies records whose retention period has expired.
2. Checks `is_legal_hold == false`. Records under active legal hold are **never** disposed.
3. Anonymizes candidate names, contact details, and clears resume files while preserving statistical aggregates.
