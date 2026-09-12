<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationCorrectiveAction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_corrective_actions';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'decision_id',
        'action_type',
        'description',
        'assigned_to_employee_id',
        'supervisor_id',
        'start_date',
        'due_date',
        'status',
        'employee_acknowledgement_status',
        'employee_acknowledged_at',
        'employee_comment',
        'completed_at',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'employee_acknowledged_at' => 'datetime',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
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

    public function decision(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationDecision::class, 'decision_id');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
