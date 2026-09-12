<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\FinancialWellnessProgram;
use App\Domains\Benefits\Models\FinancialWellnessResource;

class FinancialWellnessService
{
    public function createProgram(array $data): FinancialWellnessProgram
    {
        return FinancialWellnessProgram::create(array_merge($data, [
            'is_active' => $data['is_active'] ?? true,
        ]));
    }

    public function addResource(FinancialWellnessProgram $program, array $data): FinancialWellnessResource
    {
        return $program->resources()->create(array_merge($data, [
            'tenant_id' => $program->tenant_id,
        ]));
    }
}
