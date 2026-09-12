<?php

namespace App\Domains\Organization\Support;

final readonly class OrganizationEntityDefinition
{
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @param array<string, string> $columns
     * @param list<string> $searchable
     * @param list<string> $filterable
     * @param array<string, array<int, mixed>|string> $rules
     * @param list<string> $uniqueByTenant
     */
    public function __construct(
        public string $key,
        public string $permissionPrefix,
        public string $modelClass,
        public array $columns,
        public array $searchable,
        public array $filterable,
        public array $rules,
        public array $uniqueByTenant = [],
    ) {
    }
}
