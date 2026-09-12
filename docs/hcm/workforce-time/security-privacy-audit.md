# Security, Privacy & Audit Controls

## 1. Multi-Tenant Isolation
All tables include `tenant_id` foreign keys indexed and scoped in queries. Cross-tenant access is strictly blocked.

## 2. Biometric & Location Privacy
- Biometric templates are stored on hardware devices, never raw biometrics in application database
- GPS coordinates (where captured for mobile field punches) are strictly access-controlled and never exposed to peers

## 3. Immutable Audit Trails
- `hcm_attendance_correction_audits` preserves before/after snapshots of any modified session
- Raw event table `attendance_raw_events` is append-only and never updated with altered timestamps