<?php

namespace Tests\Unit\Platform;

use App\Domains\Platform\Services\ConfigurationService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConfigurationServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_value_overrides_tenant_and_platform_values(): void
    {
        $tenant = Tenant::factory()->create(); $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $id = (string) Str::uuid(); DB::table('configuration_definitions')->insert(['id' => $id, 'key' => 'localization.date_format', 'name' => 'Date format', 'category' => 'localization', 'data_type' => 'string', 'default_value' => json_encode('Y-m-d'), 'scopes' => json_encode(['platform','tenant','user']), 'created_at' => now(), 'updated_at' => now()]);
        $service = app(ConfigurationService::class); $service->set('localization.date_format', 'd/m/Y', 'tenant', $tenant->id, $user); $service->set('localization.date_format', 'd M Y', 'user', (string) $user->id, $user);
        $this->assertSame('d M Y', $service->get('localization.date_format', user: $user));
    }
}
