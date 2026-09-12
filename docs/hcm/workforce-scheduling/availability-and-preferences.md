# Employee Availability & Preferences

## Employee Availability Blocks

Stored in `hcm_employee_availabilities`, availability blocks capture when workers can or cannot be assigned shifts:
- `availability_type`:
  - `available`: Explicit availability declaration.
  - `unavailable`: Hard block preventing assignment unless overridden.
  - `preferred`: Desired working window.
  - `restricted`: Soft constraint (e.g. part-time student hours).
- `is_recurring` & `recurring_day_of_week`: Supports weekly recurring commitments (e.g., unavailable every Sunday).

## Privacy Guarantee
The `reason` field in availability records is strictly confidential (e.g., "Medical treatment", "Childcare").
While the scheduling engine respects the block, the private justification is never displayed to peers on team rosters or schedule views.

---

## Employee Shift Preferences

Stored in `hcm_employee_shift_preferences`, preferences allow workers to indicate shift inclinations:
- `preference_type`: `preferred` (+score bonus) or `avoid` (-score penalty).
- `shift_definition_id`: Target shift.
- `day_of_week`: Preferred or avoided day of the week.
- `priority`: Weighting factor (1 = Low, 2 = Medium, 3 = High).

Preferences are soft constraints used by the optimizer to maximize employee satisfaction without violating hard coverage requirements.
