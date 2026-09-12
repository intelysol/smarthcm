# Document Collection & Verification

## Document Lifecycle
1. **Requirement Created**: `hcm_onboarding_document_requirements` marks required items as `pending`.
2. **Employee Upload**: Employee uploads file path and name $\to$ status transitions to `submitted`.
3. **HR Review**:
   - Approve $\to$ status transitions to `verified`.
   - Reject $\to$ status transitions to `rejected` with reviewer comments.
4. **Audit Trail**: Every review step is permanently logged in `hcm_onboarding_document_reviews`.
