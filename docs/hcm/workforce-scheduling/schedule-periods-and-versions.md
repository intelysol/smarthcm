# Schedule Periods & Versioning

## Overview

Schedules are bounded by planning intervals (`RosterPeriod`).
Periods can be defined weekly, biweekly, monthly, or custom, and scoped by tenant, company, or department.

### Lifecycle States
1. `draft`: Initial creation and assignment workspace. Unlocked, unvalidated.
2. `planning`: Coverage requirements defined, optimization runs executing.
3. `under_review`: Pre-publication validation in progress.
4. `approved`: Manager approved.
5. `published`: Active schedule distributed to employees; assignments visible in self-service.
6. `locked`: Cutoff reached (e.g., 24 hours before first shift); all modifications require explicit override and audit log.

### Versioning & Audit
- Each publication increments `version` (`1 → 2 → 3...`).
- When assignments on a published schedule are modified, the system does not silently mutate records; it generates an entry in `HcmScheduleChangeLog` capturing `previous_shift_id`, `new_shift_id`, `reason`, and `changed_by`.
