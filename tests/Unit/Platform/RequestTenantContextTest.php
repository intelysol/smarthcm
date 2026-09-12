<?php

namespace Tests\Unit\Platform;

use App\Domains\Platform\Services\RequestTenantContext;
use App\Domains\Shared\Models\Tenant;
use Tests\TestCase;

class RequestTenantContextTest extends TestCase
{
    public function test_context_can_be_set_and_cleared(): void
    {
        $tenant = new Tenant(['name' => 'Tenant']);
        $tenant->id = '3c8e0df4-3b14-4d4e-859b-39d94e1f242b';
        $context = new RequestTenantContext;
        $context->set($tenant);
        $this->assertSame($tenant->id, $context->id());
        $context->clear();
        $this->assertNull($context->tenant());
    }
}
