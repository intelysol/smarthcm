# Hiring Decisions & Core HR Handoff Boundary

## Hiring Decision Prerequisites
Before a candidate can be hired:
1. Formal offer must be in `accepted` status.
2. All mandatory pre-employment background checks in `hcm_recruitment_background_checks` must have `status == 'passed'`.
3. An authorized decision maker sanctions the hire.

## Core HR Boundary
Recruitment does NOT directly write to Core HR employee tables.
Instead, `HiringHandoffService::processHire`:
1. Constructs an `EmployeeData` DTO containing candidate demographics, official email, accepted compensation, position ID, department ID, and start date.
2. Passes payload to `App\Domains\Employee\Services\EmployeeService::create`.
3. Links the generated `Employee` ID in `HcmRecruitmentHiringDecision::core_hr_employee_id`.
4. Marks the application as `hired` and triggers the Onboarding workflow.
