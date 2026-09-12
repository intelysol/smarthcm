<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitEnrollmentWindow extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'benefit_enrollment_windows';

    protected $fillable = [
        'tenant_id',
        'name',
        'plan_year',
        'start_date',
        'close_date',
        'effective_date',
        'status',
        'allow_late_enrollment',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'plan_year' => 'integer',
        'start_date' => 'date',
        'close_date' => 'date',
        'effective_date' => 'date',
        'allow_late_enrollment' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(BenefitEnrollment::class);
    }

    public function elections(): HasMany
    {
        return $this->hasMany(BenefitElection::class);
    }

    public function isOpen(): bool
    {
        $today = now()->toDateString();
        return $this->status === 'open' && $this->start_date->toDateString() <= $today && $this->close_date->toDateString() >= $today;
    }
}
