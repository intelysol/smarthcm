# AI Onboarding Guardrails & Ethical Boundaries

## Non-Autonomous Advisory Principle
AI assistance in Flow HCM Onboarding is strictly advisory (`is_advisory => true`).

## Safety Restrictions
1. **Zero Autonomous Status Mutations**: AI cannot change employee onboarding status, approve probation, or reject new hires.
2. **Adverse Inquiries Blocked**: Inquiries requesting automated probation failure, employment termination, or disciplinary action are rejected by the safety guardrail.
3. **Safe Capabilities**:
   - Welcome message personalized drafting.
   - Case readiness summarization.
   - General orientation and handbook Q&A.
