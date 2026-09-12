# Architecture Governance & Health Checks

## Automated Health Scanners
The `ArchitectureHealthAndGovernanceService` scans organizational data continuously to maintain enterprise integrity.

```mermaid
graph TD
    Scheduler[php artisan hcm:org-design-health-scan] --> Scanner[Governance Health Scanner]
    
    Scanner --> Anomaly1[Missing Family Check: Job profiles without a Job Family]
    Scanner --> Anomaly2[Title Standardization: Variations e.g. 'Sr' vs 'Senior']
    Scanner --> Anomaly3[Retired References: Active positions tied to retired jobs]
    Scanner --> Anomaly4[Orphaned Nodes: Unlinked scenario or org components]
    
    Anomaly1 --> IssueTable[org_design_health_issues Record]
    Anomaly2 --> IssueTable
    Anomaly3 --> IssueTable
    Anomaly4 --> IssueTable
```

---

## Health Issue Types & Severity
* `missing_family` (`warning`): Job profile lacks category taxonomy.
* `duplicate_title` (`info`): Potential duplicate title variation needing standardization.
* `retired_reference` (`critical`): Active position pointing to a retired job definition.
* `orphaned_position` (`warning`): Position lacking valid reporting or departmental parentage.
