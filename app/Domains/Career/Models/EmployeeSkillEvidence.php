<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'employee_skill_id', 'evidence_type', 'title', 'description',
    'document_id', 'reference_type', 'reference_id', 'verified',
    'verified_by', 'verified_at'
])]
class EmployeeSkillEvidence extends CareerModel
{
    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<EmployeeSkill, EmployeeSkillEvidence> */
    public function employeeSkill(): BelongsTo
    {
        return $this->belongsTo(EmployeeSkill::class, 'employee_skill_id');
    }

    /** @return BelongsTo<Employee, EmployeeSkillEvidence> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }
}
