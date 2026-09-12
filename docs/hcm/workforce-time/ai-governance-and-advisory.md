# AI Governance & Advisory Guardrails

## 1. Mandatory Advisory Governance
All AI intelligence in Epic 2.47 is strictly advisory:
- `is_advisory_only: true`
- `autonomous_actions_permitted: false`

## 2. Absolute Prohibitions
AI models must NEVER:
- Autonomously discipline employees
- Record misconduct or trigger terminations
- Autonomously approve unverified overtime
- Alter raw clock events or delete audit trails
- Modify payroll calculation tables directly