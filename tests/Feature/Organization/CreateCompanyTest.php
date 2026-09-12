<?php

namespace Tests\Feature\Organization;

use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_tenant_user_can_create_company(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/companies', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme People Operations',
            'legal_name' => 'Acme People Operations LLC',
            'email' => 'hr@example.com',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Acme People Operations')
            ->assertJsonPath('data.tenant_id', $tenant->id);

        $this->assertDatabaseHas('companies', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme People Operations',
            'created_by' => $user->id,
        ]);
    }

    public function test_company_names_are_unique_per_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        Company::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme People Operations',
        ]);

        $response = $this->actingAs($user)->postJson('/api/companies', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme People Operations',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }
}
