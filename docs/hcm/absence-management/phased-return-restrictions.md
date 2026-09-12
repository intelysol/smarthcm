# Phased Return & Operational Restrictions

## Phased Capacity Trajectory
Employees returning from extended leave may not be immediately capable of full-time workload. The RTW plan defines stepped capacity progressions:
```json
{
  "phases": [
    { "phase": 1, "week": 1, "capacity_percentage": 50, "hours_per_day": 4 },
    { "phase": 2, "week": 2, "capacity_percentage": 75, "hours_per_day": 6 },
    { "phase": 3, "week": 3, "capacity_percentage": 100, "hours_per_day": 8 }
  ]
}
```

## Operational Restrictions (Non-Clinical)
To safeguard employee health without exposing medical records, operational restrictions are codified:
- `no_night_shifts`: Restricts scheduling engine from assigning nocturnal shifts.
- `max_4_hours_standing`: Enforces seated or ergonomic role allocation.
- `no_heavy_lifting`: Limits physical task assignments to < 10 kg.
- `remote_only`: Restricts location requirements to home office.