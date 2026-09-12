<?php

namespace App\Domains\Career\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'employee_id', 'evidence_source', 'source_id', 'title',
    'description', 'impact_rating', 'recorded_by'
])]
class CareerTalentEvidence extends CareerModel
{
    protected function casts(): array
    {
        return [
            'impact_rating' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Employee, CareerTalentEvidence> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<Employee, CareerTalentEvidence> */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'recorded_by');
    }
}
