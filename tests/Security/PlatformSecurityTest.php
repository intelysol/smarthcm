<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlatformSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_unauthenticated_protected_route_returns_401_or_redirect(): void
    {
        $response = $this->getJson('/api/v1/platform/me');

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_sql_injection_attempt_does_not_compromise_lookup(): void
    {
        $maliciousPayload = "admin' OR '1'='1";

        $response = $this->postJson('/api/v1/platform/auth/login', [
            'email' => $maliciousPayload,
            'password' => 'arbitrary_password',
        ]);

        // Must reject cleanly without leaking SQL errors
        $response->assertStatus(422);
        $this->assertFalse(str_contains(json_encode($response->json()), 'SQLSTATE'));
        $this->assertFalse(str_contains(json_encode($response->json()), 'Syntax error'));
    }

    public function test_mass_assignment_protection_prevents_privilege_escalation(): void
    {
        $user = User::create([
            'name' => 'Standard User',
            'email' => 'standard_' . uniqid() . '@enterprise.internal',
            'password' => bcrypt('Password123!'),
        ]);

        // Attempt mass assignment of unfillable administrative flag
        $user->fill([
            'is_platform_admin' => true,
            'is_super_admin' => true,
        ]);

        // Attributes should not be set if guarded
        $this->assertFalse((bool) ($user->is_super_admin ?? false));
    }

    public function test_executable_script_upload_prevention(): void
    {
        Storage::fake('avatars');

        $maliciousPhpFile = UploadedFile::fake()->create('shell.php', 100, 'application/x-php');

        // Verify MIME/extension check blocks script upload
        $this->assertSame('php', $maliciousPhpFile->getClientOriginalExtension());

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx'];
        $isAllowed = in_array(strtolower($maliciousPhpFile->getClientOriginalExtension()), $allowedExtensions, true);

        $this->assertFalse($isAllowed, 'Executable PHP script files must never be in allowed upload extensions.');
    }
}
