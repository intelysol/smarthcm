<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationInvestigationQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_investigation_questions';

    protected $fillable = [
        'tenant_id',
        'investigation_id',
        'case_id',
        'target_role',
        'participant_id',
        'question',
        'expected_response_type',
        'answer',
        'answered_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationInvestigation::class, 'investigation_id');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCaseParticipant::class, 'participant_id');
    }
}
