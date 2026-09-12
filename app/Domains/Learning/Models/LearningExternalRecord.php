<?php

namespace App\Domains\Learning\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningExternalRecord extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hcm_learning_external_records';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'provider_name',
        'course_title',
        'completion_date',
        'duration_hours',
        'credits_earned',
        'credential_id',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'duration_hours' => 'integer',
        'credits_earned' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(LearningEvidence::class, 'external_record_id');
    }
}
