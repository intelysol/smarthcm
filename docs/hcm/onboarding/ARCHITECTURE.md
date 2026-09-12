# Onboarding Architecture & Cross-Domain Boundaries

## Architectural Invariants
1. **Orchestration Boundary**: Onboarding initiates tasks across domains (e.g., direct deposit details for Payroll, benefits enrollment, mandatory compliance courses in Learning), but each domain remains the authoritative source of record for its data.
2. **Template Versioning**: Templates and tasks are versioned (`v1`, `v2`). Active onboarding cases permanently retain the version they were instantiated with.
3. **No Duplicate Identity**: The onboarding case references `employee_id` created in Core HR. It never creates a duplicate employee master identity.
4. **Task Dependencies & Cascade**: A task is blocked until all prerequisite tasks are marked completed. When a prerequisite completes, downstream tasks are automatically evaluated and unblocked.
