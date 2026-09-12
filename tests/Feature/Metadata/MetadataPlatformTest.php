<?php

namespace Tests\Feature\Metadata;

use App\Domains\Metadata\Models\MetadataEntity;
use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetadataPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_administrator_can_design_an_entity_and_field(): void
    {
        [$tenant, $user] = $this->metadataAdministrator();
        $headers = ['X-Tenant' => $tenant->slug];
        $entity = $this->actingAs($user)->withHeaders($headers)->postJson('/api/v1/metadata/entities', ['key' => 'service_request', 'label' => 'Service Request', 'entity_type' => 'transaction', 'status' => 'draft'])->assertCreated()->json('data');

        $this->actingAs($user)->withHeaders($headers)->postJson("/api/v1/metadata/entities/{$entity['id']}/fields", ['key' => 'summary', 'label' => 'Summary', 'field_type' => 'text', 'configuration' => ['required' => true, 'max_length' => 80]])
            ->assertCreated()
            ->assertJsonPath('data.key', 'summary');

        $this->assertDatabaseHas('metadata_audits', ['tenant_id' => $tenant->id, 'action' => 'field.created']);
    }

    public function test_dynamic_records_are_validated_against_field_metadata(): void
    {
        [$tenant, $user] = $this->metadataAdministrator();
        $entity = MetadataEntity::query()->create(['tenant_id' => $tenant->id, 'key' => 'request', 'label' => 'Request', 'entity_type' => 'transaction', 'status' => 'published', 'version' => 1, 'row_version' => 1, 'created_by' => $user->id, 'updated_by' => $user->id]);
        $entity->fields()->create(['key' => 'summary', 'label' => 'Summary', 'field_type' => 'text', 'sort_order' => 0, 'configuration' => ['required' => true], 'row_version' => 1, 'created_by' => $user->id]);

        $this->actingAs($user)->withHeader('X-Tenant', $tenant->slug)->postJson("/api/v1/metadata/entities/{$entity->id}/records", ['data' => ['other' => 'value']])->assertUnprocessable()->assertJsonValidationErrors('summary');
        $this->actingAs($user)->withHeader('X-Tenant', $tenant->slug)->postJson("/api/v1/metadata/entities/{$entity->id}/records", ['data' => ['summary' => 'New request']])->assertCreated()->assertJsonPath('data.data.summary', 'New request');
    }

    public function test_metadata_endpoints_cannot_read_another_tenants_entity(): void
    {
        [$tenant, $user] = $this->metadataAdministrator();
        $other = Tenant::factory()->create();
        $entity = MetadataEntity::query()->create(['tenant_id' => $other->id, 'key' => 'private', 'label' => 'Private', 'entity_type' => 'master', 'status' => 'draft', 'version' => 1, 'row_version' => 1]);

        $this->actingAs($user)->withHeader('X-Tenant', $tenant->slug)->getJson("/api/v1/metadata/entities/{$entity->id}")->assertNotFound();
    }

    /** @return array{Tenant, User} */
    private function metadataAdministrator(): array
    {
        $tenant = Tenant::factory()->create();
        $group = PermissionGroup::query()->create(['name' => 'metadata', 'label' => 'Metadata']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        foreach (['metadata.entities.view', 'metadata.entities.manage', 'metadata.records.manage'] as $name) {
            $user->permissions()->attach(Permission::query()->create(['permission_group_id' => $group->id, 'name' => $name, 'label' => $name]));
        }

        return [$tenant, $user];
    }
}
