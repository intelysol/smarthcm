# Allocation Rules, Drivers & Validation

## Allocation Methods
1. **Direct**: 100% assignment to a single target entity.
2. **Percentage**: Split across targets using defined percentage shares.
3. **Hours-Based**: Distributed based on recorded timesheet hours.
4. **FTE-Based**: Distributed proportionally to assigned full-time equivalents.
5. **Schedule-Based**: Uses planned shift hours when actual timesheets are unavailable.

## 100% Validation Constraint
Rules using the percentage method strictly enforce that the sum of target percentages equals **100.0%**. Any configuration attempting under- or over-allocation throws an `InvalidArgumentException`.