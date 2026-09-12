<?php

namespace App\Domains\HealthSafety\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmWorkplaceAccommodation extends Model
{
    use HasUuids;

    public const STATUS_REQUESTED = 'pending';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IMPLEMENTED = 'implemented';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_COMPLETED = 'implemented';
    public const STATUS_CANCELLED = 'rejected';

    protected $table = 'hcm_workplace_accommodations';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'request_number',
        'accommodation_type',
        'title',
        'requested_adjustment',
        'decision',
        'review_date',
        'implemented_date',
        'cost_estimate',
        'decided_by',
        'decided_at',
        'decision_notes',
        'supporting_document_id',
    ];

    protected $casts = [
        'review_date' => 'date',
        'implemented_date' => 'date',
        'cost_estimate' => 'decimal:2',
        'decided_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getStatusAttribute(): ?string
    {
        if ($this->decision === 'pending') {
            return 'requested';
        }

        return $this->decision;
    }

    public function setStatusAttribute(?string $value): void
    {
        if ($value === 'requested') {
            $value = 'pending';
        }
        $this->decision = $value;
    }
}
