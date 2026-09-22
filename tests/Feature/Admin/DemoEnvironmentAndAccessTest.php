<?php

namespace Tests\Feature\Admin;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\DemoEnvironmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class DemoEnvironmentAndAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_environment_seeder_executes_and_is_idempotent(): void
    {
        $seeder = new DemoEnvironmentSeeder();
        $seeder->run();

        $this->assertDatabaseHas('tenants', [
            'slug' => 'demo-organization',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'superadmin@example.test',
            'is_platform_admin' => 1,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'hr@example.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'manager@example.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'employee@example.test',
        ]);

        // Run second time to verify idempotency
        $seeder->run();

        $this->assertEquals(1, User::where('email', 'superadmin@example.test')->count());
        $this->assertEquals(1, User::where('email', 'admin@example.test')->count());
        $this->assertEquals(1, User::where('email', 'hr@example.test')->count());
        $this->assertEquals(1, User::where('email', 'manager@example.test')->count());
        $this->assertEquals(1, User::where('email', 'employee@example.test')->count());
    }

    public function test_production_safety_guard_blocks_unauthorized_demo_seeding(): void
    {
        App::detectEnvironment(fn () => 'production');
        putenv('SEED_DEMO_USERS=false');
        $_ENV['SEED_DEMO_USERS'] = 'false';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CRITICAL SAFETY BLOCK');

        try {
            $seeder = new DemoEnvironmentSeeder();
            $seeder->run();
        } finally {
            App::detectEnvironment(fn () => 'testing');
            putenv('SEED_DEMO_USERS=true');
            $_ENV['SEED_DEMO_USERS'] = 'true';
        }
    }

    public function test_role_based_redirection_on_authentication(): void
    {
        $seeder = new DemoEnvironmentSeeder();
        $seeder->run();

        // 1. Super Admin -> /platform
        $response = $this->post('/login', [
            'email' => 'superadmin@example.test',
            'password' => 'Demo1234!@#$',
        ]);
        $response->assertRedirect('/platform');
        $this->assertAuthenticated();
        $this->post('/logout');

        // 2. Tenant Admin -> /admin/dashboard
        $response = $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'Demo1234!@#$',
        ]);
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
        $this->post('/logout');

        // 3. HR Admin -> /hr/dashboard
        $response = $this->post('/login', [
            'email' => 'hr@example.test',
            'password' => 'Demo1234!@#$',
        ]);
        $response->assertRedirect('/hr/dashboard');
        $this->assertAuthenticated();
        $this->post('/logout');

        // 4. Manager -> /manager/workbench
        $response = $this->post('/login', [
            'email' => 'manager@example.test',
            'password' => 'Demo1234!@#$',
        ]);
        $response->assertRedirect('/manager/workbench');
        $this->assertAuthenticated();
        $this->post('/logout');

        // 5. Employee -> /portal
        $response = $this->post('/login', [
            'email' => 'employee@example.test',
            'password' => 'Demo1234!@#$',
        ]);
        $response->assertRedirect('/portal');
        $this->assertAuthenticated();
        $this->post('/logout');
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $seeder = new DemoEnvironmentSeeder();
        $seeder->run();

        $employee = User::where('email', 'employee@example.test')->first();
        $employee->status = 'inactive';
        $employee->save();

        $response = $this->post('/login', [
            'email' => 'employee@example.test',
            'password' => 'Demo1234!@#$',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_last_login_at_is_recorded(): void
    {
        $seeder = new DemoEnvironmentSeeder();
        $seeder->run();

        $user = User::where('email', 'employee@example.test')->first();
        $this->assertNull($user->last_login_at);

        $this->post('/login', [
            'email' => 'employee@example.test',
            'password' => 'Demo1234!@#$',
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_tenant_admin_user_management_lifecycle(): void
    {
        $seeder = new DemoEnvironmentSeeder();
        $seeder->run();

        $admin = User::where('email', 'admin@example.test')->first();
        $tenantId = $admin->tenant_id;

        // 1. Create new user
        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Jane Colleague',
            'email' => 'jane.colleague@example.test',
            'password' => 'SecurePass123!@',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response->assertSessionHas('success');
        $newUser = User::where('email', 'jane.colleague@example.test')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals($tenantId, $newUser->tenant_id);

        // 2. Toggle status
        $response = $this->actingAs($admin)->post("/admin/users/{$newUser->id}/status");
        $response->assertSessionHas('success');
        $newUser->refresh();
        $this->assertEquals('inactive', $newUser->status);

        // 3. Reset password
        $response = $this->actingAs($admin)->post("/admin/users/{$newUser->id}/reset-password", [
            'password' => 'NewResetPass123!@',
            'password_confirmation' => 'NewResetPass123!@',
        ]);
        $response->assertSessionHas('success');
        $newUser->refresh();
        $this->assertTrue(Hash::check('NewResetPass123!@', $newUser->password));
    }

    public function test_tenant_isolation_in_user_management(): void
    {
        $seeder = new DemoEnvironmentSeeder();
        $seeder->run();

        $admin = User::where('email', 'admin@example.test')->first();

        // Create Tenant B and User B
        $tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Rival Organization',
            'slug' => 'rival-organization',
            'status' => 'active',
        ]);
        $userB = User::create([
            'name' => 'Foreign User',
            'email' => 'foreign.user@example.test',
            'password' => Hash::make('SecretPass123!'),
            'tenant_id' => $tenantB->id,
            'status' => 'active',
        ]);

        // Tenant Admin A tries to toggle status of User B -> expect 403 or 404
        $response = $this->actingAs($admin)->post("/admin/users/{$userB->id}/status");
        $this->assertTrue(in_array($response->status(), [403, 404]));

        // Tenant Admin A tries to reset password of User B -> expect 403 or 404
        $response = $this->actingAs($admin)->post("/admin/users/{$userB->id}/reset-password", [
            'password' => 'HackedPassword123!',
            'password_confirmation' => 'HackedPassword123!',
        ]);
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_help_center_routes(): void
    {
        // 1. Index
        $response = $this->get('/help');
        $response->assertStatus(200);
        $response->assertSee('Help Center');
        $response->assertSee('5-Minute Quick Start Guide');

        // 2. Category
        $response = $this->get('/help/category/getting-started');
        $response->assertStatus(200);
        $response->assertSee('Getting Started &amp; Setup', false);

        // 3. Article
        $response = $this->get('/help/article/quick-start-five-minute-guide');
        $response->assertStatus(200);
        $response->assertSee('5-Minute Quick Start Guide');
        $response->assertSee('superadmin@example.test');

        // 4. Not found category
        $response = $this->get('/help/category/non-existent-category');
        $response->assertStatus(404);

        // 5. Not found article
        $response = $this->get('/help/article/non-existent-slug');
        $response->assertStatus(404);
    }
}
