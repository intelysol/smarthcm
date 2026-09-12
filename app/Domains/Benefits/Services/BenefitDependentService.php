<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitDependent;
use App\Domains\Benefits\Models\BenefitEnrollment;
use Illuminate\Database\Eloquent\Collection;

class BenefitDependentService
{
    public function addDependent(BenefitEnrollment $enrollment, array $data): BenefitDependent
    {
        return $enrollment->dependents()->create(array_merge($data, [
            'tenant_id' => $enrollment->tenant_id,
            'employee_id' => $enrollment->employee_id,
            'is_eligible' => $data['is_eligible'] ?? true,
        ]));
    }

    public function syncFromFamilyMembers(BenefitEnrollment $enrollment): Collection
    {
        $employee = $enrollment->employee;
        $familyMembers = $employee->familyMembers ?? collect();

        foreach ($familyMembers as $member) {
            $enrollment->dependents()->firstOrCreate(
                ['family_member_id' => $member->id],
                [
                    'tenant_id' => $enrollment->tenant_id,
                    'employee_id' => $employee->id,
                    'name' => $member->name,
                    'relationship' => $member->relationship,
                    'date_of_birth' => $member->date_of_birth,
                    'is_eligible' => true,
                ]
            );
        }

        return $enrollment->dependents()->get();
    }
}
