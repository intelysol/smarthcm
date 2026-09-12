<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmSafetyIncidentInvestigation extends Model
{
    use HasUuids;

    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_APPROVED = 'approved';

    protected $table = 'hcm_safety_incident_investigations';

    protected $fillable = [
        'tenant_id',
        'safety_incident_id',
        'lead_investigator_id',
        'started_at',
        'completed_at',
        'root_cause_category',
        'root_cause_analysis',
        'findings',
        'contributing_factors',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(HcmSafetyIncident::class, 'safety_incident_id');
    }

    public function leadInvestigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_investigator_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
