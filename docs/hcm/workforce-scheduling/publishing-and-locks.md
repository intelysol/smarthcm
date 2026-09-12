# Publishing, Locks & Change Audit

## Publication Gating

The publication of a roster period transitions it from a planning artifact into an authoritative operational schedule:
- **Validation Gate**: `SchedulePublicationService` automatically runs `ScheduleValidationService`.
- If critical hard constraint violations exist, publication is **strictly blocked** unless explicitly overridden with an authorized administrative justification.
- On success:
  - Period status set to `published`.
  - Version incremented (`version + 1`).
  - Assignments marked `is_published = true`.
  - Notifications dispatched to assigned personnel.

---

## Schedule Locking

- Schedules can be locked (`is_locked = true`, `locked_at`, `locked_by`).
- Once locked, standard scheduling edits are prohibited.

---

## Post-Publication Change Auditing

To maintain complete labor compliance transparency, modifications to published assignments are logged in `HcmScheduleChangeLog`:
- Original shift ID vs new shift ID.
- Original date vs new date.
- Business reason for modification.
- ID of the modifying user.
