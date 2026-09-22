# Multi-Tenant Data Isolation & IDOR Regression Testing

## 1. Zero-Tolerance Isolation Mandate

In a multi-tenant enterprise HCM platform, tenant data leakage is a catastrophic severity-0 (P0) security event. Data isolation is not an optional feature—it is a non-negotiable architectural invariant.

### Threat Vectors:
1. **Direct Object Reference (IDOR)**: User in Tenant A supplies the UUID of an object owned by Tenant B.
2. **Missing GlobalScope**: Eloquent queries forgetting `where('tenant_id', ...)` or missing automatic multi-tenant scoping.
3. **Cross-Tenant Header Injection**: Malicious client injecting spoofed `X-Tenant-ID` headers to bypass context resolution.
4. **Shared Cache Key Collisions**: Redis cache keys lacking tenant scoping prefixes (e.g. `employee:1` vs `tenant:{id}:employee:1`).
5. **Background Queue Cross-Pollination**: Background jobs executing without restoring the tenant execution context.

---

## 2. Test Verification Blueprint (`tests/Tenant/TenantIsolationRegressionTest.php`)

All multi-tenant isolation tests follow strict adversarial patterns:

### A. Employee Directory Leakage
- **Test**: `test_employee_from_tenant_a_cannot_view_or_search_tenant_b_employees`
- **Assertion**: Searching or listing employee records from Tenant A returns only Tenant A employees. Tenant B employee records are strictly excluded (`$response->assertJsonMissing(['email' => 'victim@tenantb.com'])`).

### B. Payroll / Payslip IDOR Defense
- **Test**: `test_employee_from_tenant_a_cannot_access_tenant_b_payslips`
- **Assertion**: Querying `GET /api/me/pay/payslips/{tenant_b_payslip_id}` returns `404 Not Found` or `403 Forbidden`. The victim's salary and tax information is never exposed.

### C. Leave Request Manipulation
- **Test**: `test_manager_from_tenant_a_cannot_approve_tenant_b_leave_application`
- **Assertion**: Submitting `POST /portal/leave/applications/{tenant_b_leave_id}/approve` from a Tenant A manager fails immediately. The database status in Tenant B remains `PENDING`.

### D. Document Acknowledgment Cross-Tenant Defense
- **Test**: `test_employee_from_tenant_a_cannot_acknowledge_tenant_b_document_requirements`
- **Assertion**: Submitting `POST /api/me/documents/{tenant_b_req_id}/acknowledge` rejects unowned requirements with `404 Not Found`.

---

## 3. Regression Fix Case Study (Epic 2.68 Discovery)

During the QA execution of Epic 2.68, an IDOR vulnerability was identified and resolved:

- **Location**: `App\Domains\EmployeeExperience\Http\Controllers\EmployeeExperienceApiController::acknowledgeDocument`
- **Defect**: The controller looked up the document requirement by ID without verifying that the requirement belonged to the authenticated employee (`$requirement->employee_id !== $employee->id`).
- **Remediation**:
  ```php
  $requirement = DB::table('employee_document_requirements')
      ->where('id', $id)
      ->where('employee_id', $employee->id)
      ->first();

  if (!$requirement) {
      return response()->json([
          'success' => false,
          'error' => [
              'code' => 'RESOURCE_NOT_FOUND',
              'message' => 'Document requirement not found or not owned by employee.',
          ],
          'request_id' => (string) Str::uuid(),
      ], 404);
  }
  ```
- **Regression Verification**: Verified by automated test `TenantIsolationRegressionTest::test_employee_from_tenant_a_cannot_acknowledge_tenant_b_document_requirements`.
