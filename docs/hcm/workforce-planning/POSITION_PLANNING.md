# Position Planning & Budgeted Positions

## Position Model Architecture

A position represents a role definition within an organization structure, independent of whether an employee currently occupies it:
- A position can exist without an employee (e.g. Planned, Budgeted, Open).
- An employee may occupy a position.
- An authorized position may be temporarily frozen or permanently eliminated.

## Position Status Lifecycle

| Status | Definition | Impacts Budget? |
|---|---|---|
| `planned` | Proposed during organizational drafting | Optional |
| `budgeted` | Financially approved headcount slot | Yes |
| `open` | Active vacancy ready for recruitment | Yes |
| `occupied` | Assigned to an active employee in Core HR | Yes |
| `frozen` | Temporarily paused hiring restriction | Retains budget, paused hire |
| `cancelled` | Rejected during planning review | No |
| `eliminated` | Abolished due to organizational restructuring | Removed from budget |

## Position Budget Components

$$\text{Total Employment Cost} = \text{Base} + \text{Bonus} + \text{Benefits} + \text{Employer Statutory} + \text{Payroll Tax} + \text{Recruitment} + \text{Equipment}$$

All financial calculations use decimal columns (`decimal(15, 2)`) to eliminate floating-point approximation error.
