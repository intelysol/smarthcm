# Advisory AI Engine & Governance Guardrails

## Non-Autonomous Design Principles
The SmartHCM Shared Services AI assistant (`UnifiedServiceSearchAndAiService`) is strictly designed with **deterministic advisory guardrails**:

1. **Zero Autonomous Approvals**: AI suggestions never approve, reject, or modify transactional employee status or financial records directly.
2. **Human-in-the-Loop Review**: Suggested resolution drafts and categorization tags are provided to human agents as editable draft proposals.
3. **Transparent Advisory Flags**: All AI outputs explicitly output `is_advisory_only: true`.

---

## AI Capabilities

### 1. Intent Detection & Classification
Parses free-text employee queries (e.g., *"How do I update my tax withholding?"*) into high-level intent categories:
* `bank_details_change`
* `document_request`
* `leave_inquiry`
* `benefits_inquiry`
* `employee_relations_case`

### 2. Knowledge Deflection
Surfaces top matching published articles before a user opens a ticket, drastically reducing ticket volume for common questions.

### 3. Agent Advisory Summaries & Draft Responses
When an agent opens an inquiry, the AI summarizes employee context, flags sensitive or compliance risks, and prepares a polite, tailored response template.
