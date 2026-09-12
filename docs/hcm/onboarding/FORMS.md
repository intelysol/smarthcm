# Digital Joiner Forms & Policy Acknowledgements

## Digital Forms
Configurable forms defined in `hcm_onboarding_forms` capture structured data:
- `FORM-EMERGENCY-CONTACT`: Primary contact name, relationship, phone number.
- `FORM-DIRECT-DEPOSIT`: Bank name, routing code, account number.

Submissions are stored in `hcm_onboarding_form_submissions` linked to the onboarding case.

## Policy Acknowledgements
`hcm_onboarding_policy_acknowledgements` captures employee agreements to the Employee Handbook, Code of Conduct, and Information Security Policy, recording:
- Policy Code & Title
- Policy Version (e.g. `v1.0`)
- Exact UTC timestamp and employee client IP address
