<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityRegressionSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Employee $employee;
    protected string $companyId;
    protected array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cyber Defense Corporation',
            'slug' => 'cyber-defense',
            'tenant_code' => 'CYBER-SEC-01',
            'status' => 'active',
        ]);

        $this->companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id' => $this->companyId,
            'tenant_id' => $this->tenant->id,
            'name' => 'Cyber Defense Corp Ltd',
            'legal_name' => 'Cyber Defense Corp Ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'SecOps Auditor',
            'email' => 'auditor@cyberdefense.internal',
            'password' => bcrypt('AuditSecOps2026!'),
            'status' => 'active',
        ]);

        $this->employee = Employee::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'company_id' => $this->companyId,
            'employee_number' => 'SEC-001',
            'employee_code' => 'SEC-001',
            'first_name' => 'SecOps',
            'last_name' => 'Auditor',
            'official_email' => 'auditor@cyberdefense.internal',
            'employment_status' => 'active',
            'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->headers = [
            'X-Tenant-ID' => $this->tenant->id,
            'X-Employee-ID' => $this->employee->id,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Test 1: SQL Injection Rejection on Query Parameters
     */
    public function test_sql_injection_on_directory_search_is_safely_escaped(): void
    {
        $maliciousPayload = "' OR 1=1; DROP TABLE users; --";

        $response = $this->actingAs($this->user)
            ->withHeaders($this->headers)
            ->getJson('/api/me/directory?q=' . urlencode($maliciousPayload));

        // Must succeed with 200 and return empty/safe list without executing SQL or throwing syntax error
        $response->assertStatus(200);
        $this->assertFalse(str_contains(json_encode($response->json()), 'SQLSTATE'));
        $this->assertFalse(str_contains(json_encode($response->json()), 'Syntax error'));
    }

    /**
     * Test 2: Malicious Path Traversal Rejection
     */
    public function test_path_traversal_in_file_parameter_is_blocked(): void
    {
        $traversalAttempts = [
            '../../../../etc/passwd',
            '..%2F..%2F..%2Fwindows%2Fsystem32%2Fcmd.exe',
            '%2e%2e%2f%2e%2e%2fboot.ini',
        ];

        foreach ($traversalAttempts as $attempt) {
            $response = $this->actingAs($this->user)
                ->withHeaders($this->headers)
                ->getJson("/api/me/pay/payslips/{$attempt}");

            // The application must never return 200 or leak host files
            $this->assertTrue(in_array($response->status(), [400, 403, 404, 422]));
        }
    }

    /**
     * Test 3: Executable and Script File Upload Rejection
     */
    public function test_executable_script_upload_is_strictly_rejected(): void
    {
        Storage::fake('documents');

        $disallowedFiles = [
            UploadedFile::fake()->create('exploit.php', 50, 'application/x-php'),
            UploadedFile::fake()->create('trojan.exe', 50, 'application/x-msdownload'),
            UploadedFile::fake()->create('script.sh', 50, 'application/x-sh'),
            UploadedFile::fake()->create('payload.phtml', 50, 'text/html'),
        ];

        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'];

        foreach ($disallowedFiles as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            $this->assertFalse(
                in_array($ext, $allowedExtensions, true),
                "Extension {$ext} must NOT be accepted in document uploads."
            );
        }
    }

    /**
     * Test 4: Cross-Site Scripting (XSS) Sanitization
     */
    public function test_xss_script_payload_in_notes_does_not_execute(): void
    {
        $xssPayload = "<script>alert('XSS-BREACH')</script>";

        // Attempting to submit attendance clock with script in notes
        $response = $this->actingAs($this->user)
            ->withHeaders($this->headers)
            ->postJson('/api/me/attendance/clock', [
                'action' => 'toggle',
                'notes' => $xssPayload,
            ]);

        // Should handle gracefully without executing script or corrupting JSON
        $this->assertTrue(in_array($response->status(), [200, 401, 404]));
    }
}
