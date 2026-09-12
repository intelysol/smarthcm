# AI Governance, Security, Privacy & Audit

## Ethical Boundaries & Privacy Guardrails
1. **Advisory Role**: AI insights are strictly informational (`is_advisory_only: true`). AI is prohibited from initiating employment actions or modifying accounting records.
2. **Confidentiality & Small Group Suppression**: If an organizational slice contains fewer than 3 employees, individual salary lines are suppressed to prevent unauthorized compensation inference.
3. **Immutable Audit Trails**: Every snapshot lock, allocation execution, and rule modification logs an audit entry with user attribution, timestamps, and previous/new state diffs.