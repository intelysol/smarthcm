<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitLifeEvent extends Model
{
    use HasUuids;

    protected $table = 'benefit_life_events';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'life_event_type_id',
        'event_type',
        'event_date',
        'election_window_end',
        'documentation_deadline',
        'documentation_status',
        'affected_plans',
        'status',
        'description',
        'document_id',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'election_window_end' => 'date',
        'documentation_deadline' => 'date',
        'affected_plans' => 'array',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lifeEventType(): BelongsTo
    {
        return $this->belongsTo(BenefitLifeEventType::class, 'life_event_type_id');
    }

    public function elections()
    {
        return $this->hasMany(BenefitElection::class, 'benefit_life_event_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
