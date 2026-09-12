# Cross-Domain Platform Integrations

## Upstream & Downstream Touchpoints
- **Recruitment (Epic 2.26)**: Listens for offer acceptance and background check clearance to trigger employee creation and onboarding case initialization.
- **Core HR (Epic 2.1 & 2.2)**: Authoritative master for Employee, Department, and Position records.
- **Payroll (Epic 2.15)**: Consumes direct deposit bank details verified during onboarding.
- **Benefits (Epic 2.16)**: Receives benefits enrollment election tasks.
- **Learning (Epic 2.25)**: Tracks completion of mandatory compliance and security training courses.
- **IT & Provisioning**: Dispatches hardware and account setup requests (`hcm_onboarding_provisioning_requests`).
