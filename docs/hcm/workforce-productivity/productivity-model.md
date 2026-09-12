# Productivity Measurement Model

## Basic Formulation
$$\text{Productivity} = \frac{\text{Operational Output}}{\text{Productive Labor Hours}}$$

### Metric Types Supported:
- **Volume Metrics**: Units completed, cases resolved, tickets closed, transactions processed.
- **Time Metrics**: Scheduled hours, attended hours, productive hours, waiting hours, idle hours.
- **Quality Metrics**: First-pass yield, defect rate, rework count, quality compliance score.
- **Economic Metrics**: Revenue generated per productive hour, labor cost per output unit.

### Safe Division Rule:
If productive labor hours $\le 0$ or output is null, the result is strictly `null` (`N/A`). Zero is never assumed when data is absent.
