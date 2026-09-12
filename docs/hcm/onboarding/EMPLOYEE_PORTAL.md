# Employee Onboarding Portal & Self-Service

## Employee Experience
The portal at `/my/onboarding` offers:
- Personalized welcome banner with official joining date and department.
- Real-time completion progress meter.
- Step-by-step checklist of pending tasks.
- Instant submission of digital forms and policy acknowledgements.
- First-day orientation schedule.

## Secure Identity Resolution
The portal APIs (`/api/v1/me/onboarding/*`) strictly derive identity from the authenticated session context (`$request->user()->employee_id`). Browser-supplied IDs are rejected.
