<?php

namespace App\Domains\Employee\Support;

final readonly class EmployeeSectionDefinition
{
    public function __construct(
        public string $key,
        public string $permission,
        public string $modelClass,
        public array $rules,
    ) {
    }
}
