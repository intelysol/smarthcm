# Cross-Domain Reconciliation

## 1. Concept
Cross-domain reconciliation routines verify alignment between Core HR and peripheral operational domains without overwriting authoritative domain data.

## 2. Supported Reconciliation Rules
- **Core HR $\leftrightarrow$ Payroll**: Identifies active employees who are missing active compensation or bank routing setup in Payroll.
- **Core HR $\leftrightarrow$ Benefits**: Verifies that enrolled employees maintain active employment status and valid plan eligibility.
- **Core HR $\leftrightarrow$ Compliance**: Identifies employees whose work visas or statutory licenses are missing or expired.
- **Core HR $\leftrightarrow$ Documents**: Identifies missing mandatory employment contracts or government IDs.
- **Core HR $\leftrightarrow$ Expenses**: Verifies expense cost-center routing matches employee department assignments.

## 3. Discrepancy Statuses
- `matched`: Records are perfectly aligned across domains.
- `missing`: Reference exists in Core HR but target domain configuration is absent.
- `mismatch`: Values (e.g. salary currency, department code) differ between domains.
- `duplicate`: Multiple overlapping records detected in target domain.
