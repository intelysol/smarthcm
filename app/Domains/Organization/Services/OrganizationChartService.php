<?php

namespace App\Domains\Organization\Services;

use App\Domains\Organization\Models\Company;

class OrganizationChartService
{
    /**
     * @return array<string, mixed>
     */
    public function forTenant(string $tenantId): array
    {
        $companies = Company::query()
            ->where('tenant_id', $tenantId)
            ->with('branches.businessUnits.departments.sections.teams')
            ->orderBy('name')
            ->get();

        return [
            'nodes' => $companies->map(fn (Company $company): array => [
                'id' => $company->id,
                'type' => 'company',
                'label' => $company->name,
                'children' => $company->branches->map(fn ($branch): array => [
                    'id' => $branch->id,
                    'type' => 'branch',
                    'label' => $branch->branch_name,
                    'children' => $branch->businessUnits->map(fn ($unit): array => [
                        'id' => $unit->id,
                        'type' => 'business_unit',
                        'label' => $unit->name,
                        'children' => $unit->departments->map(fn ($department): array => [
                            'id' => $department->id,
                            'type' => 'department',
                            'label' => $department->department_name,
                            'children' => $department->sections->map(fn ($section): array => [
                                'id' => $section->id,
                                'type' => 'section',
                                'label' => $section->section_name,
                                'children' => $section->teams->map(fn ($team): array => [
                                    'id' => $team->id,
                                    'type' => 'team',
                                    'label' => $team->team_name,
                                    'children' => [],
                                ])->values()->all(),
                            ])->values()->all(),
                        ])->values()->all(),
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
