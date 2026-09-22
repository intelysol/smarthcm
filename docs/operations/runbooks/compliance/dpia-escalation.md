# Operational Runbook: Data Protection Impact Assessment (DPIA) Escalation

## Severity: P2 (Privacy Risk Assessment)
## Triggers: High-Risk Processing (Biometric Data, Automated Profiling, Systematic Monitoring)

### 1. Mandatory Assessment Triggers
In accordance with GDPR Article 35, a DPIA is required before initiating:
* Biometric authentication or automated timekeeping systems
* AI-driven workforce scheduling or employee productivity evaluations
* Cross-border personal data transfers outside adequacy decision zones

### 2. Assessment Evaluation & Risk Scoring
1. Privacy Lead submits DPIA via `/admin/compliance-governance`:
   * Processing Activity reference
   * Necessity & proportionality analysis
   * Potential threats to data subject freedoms (identity theft, surveillance, unauthorized disclosure)
   * Proposed technical mitigations (tokenization, encryption at rest, access restrictions)
2. Service evaluates residual risk level:
   * **LOW / MEDIUM**: Eligible for standard DPO approval.
   * **HIGH / CRITICAL**: Triggers mandatory Privacy Review Board review.

### 3. Consultation & Sign-Off
1. If high residual risk cannot be mitigated, initiate formal supervisory authority consultation prior to deployment.
2. Record DPO formal approval and attach DPIA report in `privacy_impact_assessments`.
