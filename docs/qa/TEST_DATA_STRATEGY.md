# Enterprise Test Data Strategy & Factory Patterns

## 1. Principles of Test Data Generation

In SmartHCM, test data generation must balance speed, realistic data shapes, and strict multi-tenant boundary isolation.

### Core Principles:
1. **UUID Primacy**: Primary keys across all domain entities (`Tenant`, `Employee`, `PayrollRun`, `Company`, `LeaveRequest`) are standard RFC 4122 Version 4 UUIDs or ULIDs.
2. **Minimal Viable Graph (MVG)**: Build only the entity graph required for the assertion. Avoid seeding 50 related records when testing a single endpoint.
3. **Deterministic Fixtures**: Avoid non-deterministic pseudo-random values in assertions (e.g. asserting static strings against randomized text).
4. **Tenant-Bound Seeding**: Never instantiate an enterprise domain entity without explicitly associating it with a valid `tenant_id`.

---

## 2. Standard Multi-Tenant Test Graph Pattern

When setting up a test case representing a tenant context, follow this minimal viable graph pattern:

```php
protected Tenant $tenant;
protected User $user;
protected Employee $employee;
protected string $companyId;

protected function setUp(): void
{
    parent::setUp();

    // 1. Root Tenant Entity
    $this->tenant = Tenant::create([
        'id' => (string) Str::uuid(),
        'name' => 'Apex Vanguard Corp',
        'slug' => 'apex-vanguard',
        'tenant_code' => 'APEX-001',
        'status' => 'active',
    ]);

    // 2. Company / Legal Entity
    $this->companyId = (string) Str::uuid();
    DB::table('companies')->insert([
        'id' => $this->companyId,
        'tenant_id' => $this->tenant->id,
        'name' => 'Apex Vanguard Global Ltd',
        'legal_name' => 'Apex Vanguard Global Ltd',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3. User Identity
    $this->user = User::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Elena Rostova',
        'email' => 'elena.rostova@apexvanguard.internal',
        'password' => bcrypt('SecurePassword2026!'),
        'status' => 'active',
    ]);

    // 4. Employee Domain Profile
    $this->employee = Employee::create([
        'id' => (string) Str::uuid(),
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'company_id' => $this->companyId,
        'employee_number' => 'EMP-101',
        'employee_code' => 'EMP-101',
        'first_name' => 'Elena',
        'last_name' => 'Rostova',
        'official_email' => 'elena.rostova@apexvanguard.internal',
        'employment_status' => 'active',
        'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
    ]);
}
```

---

## 3. Two-Tenant Boundary Pattern (Isolation & IDOR Testing)

For cross-tenant and IDOR test scenarios, always generate two distinct tenant trees:
- **Tenant A**: The authenticated persona (e.g. `tenant_a`, `user_a`, `employee_a`)
- **Tenant B**: The victim persona (e.g. `tenant_b`, `user_b`, `employee_b`, `resource_b`)

```text
[Tenant A Hierarchy]               [Tenant B Hierarchy]
  ├── Tenant A                       ├── Tenant B
  ├── User A                         ├── User B
  ├── Employee A                     ├── Employee B
  └── Resource A                     └── Resource B (Target for IDOR Probe)
```

The test authenticates as **User A** and attempts:
1. Direct read (`GET /api/.../{resource_b_id}`)
2. Unauthorized mutation (`PUT /api/.../{resource_b_id}`)
3. Unauthorized action (`POST /api/.../{resource_b_id}/approve`)
4. Unauthorized deletion (`DELETE /api/.../{resource_b_id}`)

In all cases, the platform must reject the request with `404 Not Found` or `403 Forbidden` and zero state leakage.
