# Onboarding Data Model & Database Schema

## Normalized Table Schema
All tables are multi-tenant scoped with `tenant_id` foreign keys and UUID primary keys.

1. **`hcm_onboarding_templates`**: Master template definitions with condition filters (department, location, worker type).
2. **`hcm_onboarding_template_versions`**: Immutable snapshots of templates.
3. **`hcm_onboarding_template_tasks`**: Task definitions with relative offsets (negative for preboarding, positive for post-start).
4. **`hcm_onboarding_cases`**: The onboarding instance per new hire.
5. **`hcm_onboarding_case_tasks`**: Concrete task instances with due dates, assignees, and statuses.
6. **`hcm_onboarding_task_dependencies`**: Self-referencing prerequisite table enforcing sequencing.
7. **`hcm_onboarding_document_requirements`**: Document collection checklist per case.
8. **`hcm_onboarding_document_reviews`**: Verification review records.
9. **`hcm_onboarding_forms`**: Joiner form schemas (emergency contact, direct deposit).
10. **`hcm_onboarding_form_submissions`**: Stored form responses per case.
11. **`hcm_onboarding_policy_acknowledgements`**: Policy acknowledgement records with versions and IP stamps.
12. **`hcm_onboarding_provisioning_requests`**: IT equipment and access requests.
13. **`hcm_onboarding_buddy_assignments`**: Peer mentor / onboarding buddy records.
14. **`hcm_onboarding_probations`**: Probation lifecycle tracking (30 to 90 days).
15. **`hcm_onboarding_probation_reviews`**: Formal probation outcome reviews.
16. **`hcm_onboarding_surveys`**: Post-onboarding new hire satisfaction ratings.
