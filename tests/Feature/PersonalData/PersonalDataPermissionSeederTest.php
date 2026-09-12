<?php

namespace Tests\Feature\PersonalData;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Database\Seeders\PersonalDataPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalDataPermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_data_permissions_seeded_correctly(): void
    {
        $this->seed(PersonalDataPermissionSeeder::class);

        $group = PermissionGroup::where('name', 'personal_data')->first();
        $this->assertNotNull($group);

        $expectedPermissions = [
            'personal_data.view',
            'personal_data.manage',
            'personal_data.view_sensitive',
            'personal_data.manage_sensitive',
            'personal_data.address.manage',
            'personal_data.emergency_contact.manage',
            'personal_data.dependent.manage',
            'personal_data.identifier.manage',
            'personal_data.bank_request.submit',
            'personal_data.bank_request.approve',
            'personal_data.change_request.manage',
            'personal_data.verification.manage',
            'personal_data.quality.view',
            'personal_data.quality.recalculate',
            'personal_data.bulk.manage',
        ];

        foreach ($expectedPermissions as $perm) {
            $exists = Permission::where('name', $perm)
                ->where('permission_group_id', $group->id)
                ->exists();
            $this->assertTrue($exists, "Permission {$perm} not found in database.");
        }
    }
}
