# Analytics Privacy & Anonymity Safeguards

## Anonymous Survey Protection
To guarantee employee psychological safety and prevent de-anonymization through selective filtering, Flow HCM enforces:
1. **Minimum Anonymous Group Threshold**: Configured to a minimum of **5 respondents** (`MINIMUM_ANONYMOUS_GROUP_SIZE = 5`).
2. **Small Cell Suppression**: Any dimensional slice (e.g. specific department, branch, or tenure band) with fewer than 5 respondents automatically suppresses eNPS, favorability scores, and response distributions.
3. **No Direct Identity Joins**: Survey responses are decoupled from user identifiers and cannot be traced back to employee profile tables.

## Employee Relations Confidentiality
Employee Relations analytics are strictly **Aggregate-Only**:
- Case counts, aging trends, and status distributions are visible only to authorized users (`hcm.analytics.er.aggregate.view`).
- Confidential investigation notes, witness statements, allegations, and party identities are never exposed in analytics queries.
