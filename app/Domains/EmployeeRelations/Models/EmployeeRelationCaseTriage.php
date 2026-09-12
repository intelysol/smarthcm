<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationCaseTriage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_case_triage';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'triaged_by',
        'recommended_case_type_id',
        'recommended_priority',
        'recommended_severity',
        'requires_investigation',
        'required_investigator_type',
        'conflict_of_interest_checked',
        'escalation_required',
        'escalation_reason',
        'triage_notes',
        'triaged_at',
    ];

    protected function casts(): array
    {
        return [
            'requires_investigation' => 'boolean',
            'conflict_of_interest_checked' => 'boolean',
            'escalation_required' => 'boolean',
            'triaged_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function triagedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triaged_by');
    }

    public function recommendedCaseType(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCaseType::class, 'recommended_case_type_id');
    }
}
