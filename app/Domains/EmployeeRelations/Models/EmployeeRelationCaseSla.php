<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationCaseSla extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_case_slas';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'sla_type',
        'target_hours',
        'due_at',
        'breached_at',
        'reminder_sent_at',
        'escalation_sent_at',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_hours' => 'integer',
            'due_at' => 'datetime',
            'breached_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'escalation_sent_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function isBreached(): bool
    {
        return $this->status === 'breached' || ($this->status === 'pending' && $this->due_at->isPast());
    }
}
