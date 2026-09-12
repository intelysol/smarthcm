# Shift Definitions & Patterns

## Shift Model

In SmartHCM, shifts represent bounded working intervals configured per tenant.
They are modelled via `ShiftDefinition` (`shift_definitions` table):

### Supported Shift Types
- **Normal**: Fixed working hours within the same calendar day (e.g. 08:00–16:00).
- **Overnight (Night Shift)**: Cross-midnight shifts (e.g. 23:00–07:00). Automatically identified via `is_night_shift` or `end_time < start_time`.
- **Flexible**: Core hours requirement with variable window (e.g. core hours 10:00–14:00, required 480 minutes daily).
- **Split Shift**: Double-interval workday with second start and end time (`split_second_start`, `split_second_end`).

### Break & Punctuality Configuration
- `grace_period_minutes`: Window before arrival is logged as late (default 15 minutes).
- `late_threshold_minutes`: Threshold for minor vs major lateness (default 30 minutes).
- `duration_minutes`: Net planned productive shift duration.
- `ShiftBreak`: Configurable paid and unpaid break intervals per shift.

---

## Shift Patterns & Rotation Cycles

Shift patterns (`ShiftPattern` and `ShiftPatternDay`) model recurring workforce schedules:
- **Fixed Pattern**: Same shift schedule repeated weekly (e.g., Mon–Fri Morning, Sat–Sun Off).
- **Rotating Pattern**: Cyclical rotations (e.g., 4 Days On / 2 Days Off, or rotating Morning → Evening → Night → Off).
- **Custom Cycle**: User-defined cycle length (e.g., 14-day biweekly or 28-day continental rotations).
