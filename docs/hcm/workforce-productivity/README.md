# HCM Workforce Productivity, Performance-to-Cost, Labor Efficiency & Workforce ROI Intelligence

**Epic:** 2.50  
**Module:** `app/Domains/WorkforceProductivity/`  
**Architecture:** Multi-Tenant Modular Monolith | DDD | API-First | Non-Surveillance  

---

## 1. Overview

SmartHCM Epic 2.50 delivers enterprise workforce productivity, labor efficiency, performance-to-cost, and workforce ROI intelligence. It converts operational labor time and output events into actionable economic intelligence without duplicating underlying attendance, payroll, or capacity engines.

### Key Capabilities
- **Tenant-Configurable Metric Framework**: Versioned formula definitions with immutable historical records.
- **Productive Time vs. Attended Time**: Clear classification of active productive time, training, meetings, administration, waiting buffer, and absence.
- **Labor Efficiency & Output Economics**: Output per labor hour, output per FTE, and direct integration with Epic 2.49 Workforce Cost (cost per unit, output per workforce dollar).
- **Schedule Effectiveness**: Triangulation of scheduled hours vs. attended punches vs. productive hours vs. required capacity.
- **Overtime, Absence & Turnover Economics**: Productivity drag quantification for overtime fatigue, unplanned absence clusters, open vacancy drag, and new-hire ramp-up curves.
- **Workforce Investment ROI**: Evaluates training (L&D), recruitment ramp-up, automation, and organizational investments with explicit `CORRELATION` vs `CAUSAL` labels.
- **Ethical AI & Anti-Surveillance**: Prohibits keystroke, webcam, or screen surveillance. Employs non-punitive operational bottleneck diagnostics with small group privacy suppression.

---

## 2. Documentation Index

1. [Architecture & Bounded Contexts](architecture.md)
2. [Domain Ownership Boundaries](domain-boundaries.md)
3. [Productivity Measurement Model](productivity-model.md)
4. [Metric Definition Framework & Versioning](metric-definition-framework.md)
5. [Productive Time Classification](productive-time-model.md)
6. [Workforce Utilization](workforce-utilization.md)
7. [Schedule Effectiveness](schedule-effectiveness.md)
8. [Labor Efficiency & Cost Integration](labor-efficiency.md)
9. [Cost-Productivity Integration (Epic 2.49)](cost-productivity-integration.md)
10. [Overtime, Absence & Turnover Economics](overtime-absence-turnover-economics.md)
11. [Workforce Investment ROI](workforce-investment-roi.md)
12. [Scenario Modeling & What-If Simulations](scenario-modeling.md)
13. [Forecasting & Internal Benchmarking](forecasting-and-benchmarking.md)
14. [Anti-Surveillance, Privacy & Contextual Fairness](anti-surveillance-privacy-fairness.md)
15. [Advisory AI Governance & Explainability](advisory-ai-governance.md)
16. [Data Quality, Zero-Denominator Safety & Provenance](data-quality-provenance.md)
17. [Operational Runbook & Queue Management](operational-runbook.md)
