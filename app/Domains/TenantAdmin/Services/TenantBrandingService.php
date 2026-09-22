<?php

namespace App\Domains\TenantAdmin\Services;

use App\Domains\TenantAdmin\Models\HcmTenantBranding;

class TenantBrandingService
{
    public function getBranding(string $tenantId): HcmTenantBranding
    {
        return HcmTenantBranding::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'company_name' => 'SmartHCM Enterprise Client',
                'logo_url' => '/images/branding/default-logo.svg',
                'favicon_url' => '/favicon.ico',
                'primary_color' => '#4f46e5',
                'secondary_color' => '#0ea5e9',
                'accent_color' => '#10b981',
                'portal_title' => 'SmartHCM Enterprise Portal',
            ]
        );
    }

    public function updateBranding(string $tenantId, array $data): HcmTenantBranding
    {
        $branding = $this->getBranding($tenantId);
        $branding->update(array_filter([
            'company_name' => $data['company_name'] ?? $branding->company_name,
            'logo_url' => $data['logo_url'] ?? $branding->logo_url,
            'favicon_url' => $data['favicon_url'] ?? $branding->favicon_url,
            'primary_color' => $data['primary_color'] ?? $branding->primary_color,
            'secondary_color' => $data['secondary_color'] ?? $branding->secondary_color,
            'accent_color' => $data['accent_color'] ?? $branding->accent_color,
            'custom_css' => $data['custom_css'] ?? $branding->custom_css,
            'portal_title' => $data['portal_title'] ?? $branding->portal_title,
        ]));

        return $branding->fresh();
    }
}
