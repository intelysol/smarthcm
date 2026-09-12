<?php

namespace Tests\Feature\Rules;

use App\Domains\Rules\Models\BusinessRule;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_rule_tester_returns_action_intents_without_side_effects(): void
    {
        [$tenant, $user] = $this->ruleAdministrator();
        $rule = BusinessRule::query()->create(['tenant_id' => $tenant->id, 'key' => 'high_value_notification', 'name' => 'High value notification', 'domain' => 'sales', 'category' => 'notification', 'trigger' => 'record.created', 'priority' => 10, 'version' => 1, 'status' => 'draft', 'execution_mode' => 'synchronous', 'conditions' => ['field' => 'data.total', 'operator' => 'greater_than', 'value' => 100], 'actions' => [['type' => 'send_notification', 'template' => 'high-value']], 'created_by' => $user->id, 'updated_by' => $user->id]);

        $this->actingAs($user)->withHeader('X-Tenant', $tenant->slug)->postJson("/api/v1/rules/{$rule->id}/test", ['data' => ['total' => 125]])
            ->assertOk()
            ->assertJsonPath('data.matched', true)
            ->assertJsonPath('data.intents.0.type', 'send_notification');
        $this->assertDatabaseHas('rule_executions', ['business_rule_id' => $rule->id, 'status' => 'succeeded', 'execution_mode' => 'dry_run']);
    }

    public function test_rule_lifecycle_enforces_the_approved_transition_path(): void
    {
        [$tenant, $user] = $this->ruleAdministrator();
        $rule = BusinessRule::query()->create(['tenant_id' => $tenant->id, 'key' => 'routing', 'name' => 'Routing', 'domain' => 'platform', 'category' => 'decision', 'trigger' => 'record.created', 'priority' => 100, 'version' => 1, 'status' => 'draft', 'execution_mode' => 'synchronous', 'conditions' => ['field' => 'data.status', 'operator' => 'equals', 'value' => 'new'], 'actions' => [], 'created_by' => $user->id, 'updated_by' => $user->id]);
        $headers = ['X-Tenant' => $tenant->slug];
        $this->actingAs($user)->withHeaders($headers)->postJson("/api/v1/rules/{$rule->id}/transition", ['status' => 'published'])->assertUnprocessable();
        $this->actingAs($user)->withHeaders($headers)->postJson("/api/v1/rules/{$rule->id}/transition", ['status' => 'under_review'])->assertOk();
        $this->actingAs($user)->withHeaders($headers)->postJson("/api/v1/rules/{$rule->id}/transition", ['status' => 'approved'])->assertOk();
        $this->actingAs($user)->withHeaders($headers)->postJson("/api/v1/rules/{$rule->id}/transition", ['status' => 'published'])->assertOk()->assertJsonPath('data.status', 'published');
    }

    /** @return array{Tenant, User} */
    private function ruleAdministrator(): array
    {
        $tenant = Tenant::factory()->create();
        $group = PermissionGroup::query()->create(['name' => 'rules', 'label' => 'Rules']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        foreach (['rules.view', 'rules.manage', 'rules.execute'] as $name) { $user->permissions()->attach(Permission::query()->create(['permission_group_id' => $group->id, 'name' => $name, 'label' => $name])); }
        return [$tenant, $user];
    }
}
