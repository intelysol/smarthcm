# Open Shifts & Bidding

## Purpose

When operational demand spikes or uncovered slots remain after primary scheduling, managers can broadcast **Open Shifts** to eligible personnel instead of imposing involuntary assignments.

## Workflow

1. **Publish Open Shift**:
   - Manager creates an open shift (`HcmOpenShift`) specifying date, shift, required skill, and available slots.
2. **Employee Bidding**:
   - Eligible workers submit bids (`HcmOpenShiftBid`).
   - `ScheduleEligibilityService` computes an `eligibility_score` (100% minus penalties for soft warnings such as consecutive days).
3. **Awarding & Fulfillment**:
   - The manager reviews bids and awards the shift.
   - A scheduled `RosterAssignment` is automatically created.
   - `slots_filled` is incremented. When `slots_filled == slots_total`, the shift is marked `filled`, and remaining pending bids are cleanly set to `declined`.
