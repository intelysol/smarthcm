<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use Illuminate\Database\Eloquent\Collection;

class ServiceCatalogService
{
    public function getCategories(string $tenantId): Collection
    {
        return HrServiceCategory::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['services' => function ($q) {
                $q->where('status', 'active')->orderBy('name', 'asc');
            }])
            ->orderBy('display_order', 'asc')
            ->get();
    }

    public function getPopularServices(string $tenantId, int $limit = 6): Collection
    {
        return HrServiceDefinition::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('is_popular', true)
            ->with(['category'])
            ->limit($limit)
            ->get();
    }

    public function searchServices(string $tenantId, string $query): Collection
    {
        return HrServiceDefinition::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('service_code', 'like', "%{$query}%");
            })
            ->with(['category'])
            ->get();
    }

    public function getServiceWithForm(HrServiceDefinition $service): array
    {
        $version = $service->versions()
            ->where('is_active', true)
            ->orderBy('version_number', 'desc')
            ->first();

        $formDefinition = $version?->formDefinition;

        return [
            'service' => $service,
            'version' => $version,
            'form_schema' => $formDefinition?->schema ?? [],
            'sla_policy' => $service->slaPolicy,
        ];
    }
}
