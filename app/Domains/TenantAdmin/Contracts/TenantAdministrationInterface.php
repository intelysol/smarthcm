<?php

namespace App\Domains\TenantAdmin\Contracts;

interface TenantAdministrationInterface
{
    public function getAdminDashboard(string $tenantId): array;

    public function getSystemHealth(string $tenantId): array;
}
