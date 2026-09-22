<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTestSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected string $password = 'SecurePassword2026!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Global Defense',
            'slug' => 'acme-global',
            'tenant_code' => 'ACM-SEC-01',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Alexander Hayes',
            'email' => 'alexander.hayes@acmeglobal.internal',
            'password' => Hash::make($this->password),
            'status' => 'active',
        ]);
    }

    public function test_web_login_with_valid_credentials_succeeds_and_establishes_session(): void
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => $this->password,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/portal');
        $this->assertAuthenticatedAs($this->user);
        $this->assertSame($this->tenant->id, session('tenant_uuid'));
    }

    public function test_web_login_with_invalid_password_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => $this->user->email,
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_web_login_with_nonexistent_email_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'unknown.ghost@acmeglobal.internal',
            'password' => $this->password,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_web_logout_invalidates_session_and_clears_authentication(): void
    {
        // First log in
        $this->actingAs($this->user);
        $this->assertAuthenticated();

        // Perform logout
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_unauthenticated_api_request_returns_standard_unauthenticated_envelope(): void
    {
        $response = $this->getJson('/api/v1/platform/me');

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error.code', 'UNAUTHENTICATED');
        $this->assertNotEmpty($response->json('request_id'));
    }

    public function test_already_authenticated_user_visiting_login_page_redirects_to_portal(): void
    {
        $response = $this->actingAs($this->user)->get('/login');

        $response->assertStatus(302);
        $response->assertRedirect('/portal');
    }

    public function test_api_login_validation_rejects_missing_fields_with_standard_envelope(): void
    {
        $response = $this->postJson('/api/v1/platform/auth/login', []);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
        $this->assertArrayHasKey('email', $response->json('error.details'));
        $this->assertArrayHasKey('password', $response->json('error.details'));
        $this->assertNotEmpty($response->json('request_id'));
    }
}
