# Job Families & Sub-Families

## Overview
Job Families represent broad occupational disciplines within the enterprise, while Job Sub-Families provide specialized groupings.

---

## Default Job Families & Sub-Families
* **Technology & Engineering**
  * Software Engineering
  * Cloud & Infrastructure
  * Cybersecurity
  * Data Engineering & AI
  * IT Service & Support
* **Finance & Accounting**
  * Corporate Accounting
  * Financial Planning & Analysis (FP&A)
  * Treasury & Tax
  * Internal Audit & Risk
* **Human Resources**
  * Talent Acquisition
  * HR Operations & Shared Services
  * Total Rewards (Compensation & Benefits)
  * Talent & Leadership Development
  * Employee Relations & Compliance
* **Sales & Business Development**
  * Enterprise Sales
  * Account Management
  * Sales Engineering
  * Channel Partnerships
* **Operations & Supply Chain**
  * Logistics & Fulfillment
  * Procurement
  * Quality Assurance & Manufacturing

---

## Governance Rules
* Every active Job Profile must map to an authoritative `JobFamily`.
* Anomaly scans flag any unmapped or orphaned jobs as `missing_family` issues in `org_design_health_issues`.
