<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'instructor_type', 'employee_id', 'provider_id', 'name', 'email', 'bio', 'expertise', 'status'])]
class LearningInstructor extends LearningModel
{
    protected function casts(): array
    {
        return [
            'expertise' => 'array',
        ];
    }

    /** @return BelongsTo<Employee, LearningInstructor> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningProvider, LearningInstructor> */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(LearningProvider::class, 'provider_id');
    }

    /** @return HasMany<LearningSession> */
    public function sessions(): HasMany
    {
        return $this->hasMany(LearningSession::class, 'instructor_id');
    }
}
