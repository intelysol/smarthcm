# Privacy & Sensitive Information Protection

## Banking & Tax Data Masking
Direct deposit account numbers and tax identification numbers collected during onboarding are strictly masked by `OnboardingSecurityService::maskSensitiveBankingData`:
- `1234567890` $\to$ `******7890`
- Raw banking details are inaccessible in manager views, search results, or exported audit logs.
