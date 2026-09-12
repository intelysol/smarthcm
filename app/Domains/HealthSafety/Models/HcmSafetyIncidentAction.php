<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmSafetyIncidentAction extends Model
{
    use HasUuids;

    public const TYPE_CORRECTIVE = 'corrective';
    public const TYPE_PREVENTIVE = 'preventive';

    public const HIERARCHY_ADMINISTRATIVE = 'administrative_controls';

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'hcm_safety_incident_actions';

    protected $fillable = [
        'tenant_id',
        'safety_incident_id',
        'action_type',
        'title',
        'description',
        'assigned_to',
        'due_date',
        'priority',
        'status',
        'completed_date',
        'verified_by',
        'verified_at',
        'evidence_document_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(HcmSafetyIncident::class, 'safety_incident_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
