# Organization Design Scenarios & Reorganization Planning

## Multi-Dimensional Organizational Hierarchy
SmartHCM supports flexible, multi-dimensional organizational modeling without forcing a rigid universal structure:

```text
Enterprise
    ↓
Company / Legal Entity
    ↓
Business Unit
    ↓
Division
    ↓
Department
    ↓
Section
    ↓
Team
```

---

## Non-Destructive Scenario Modeling
Reorganization planning allows HR leaders to evaluate restructuring proposals without mutating live Core HR records:

1. **Current State (Live)**: Authoritative operational units (`companies`, `departments`, `sections`, `teams`).
2. **Scenario Clone**: Live organizational nodes are cloned into `org_design_scenario_nodes` with `action_type = 'existing'`.
3. **Proposed Mutations**:
   - `add`: Staging new business units, divisions, or departments.
   - `merge`: Consolidating redundant operational entities.
   - `split`: Dividing large functional units into specialized entities.
   - `modify`: Adjusting parent reporting structures or target headcounts.
   - `remove`: Phasing out decommissioned departments or branches.
4. **Current vs. Future State Comparison**: Computes delta statistics (nodes added, nodes merged, nodes modified, nodes removed) for stakeholder review and board approval.
5. **Enactment**: Once approved, structural changes flow to Core HR exclusively through formal personnel actions and approved organizational change transactions.
