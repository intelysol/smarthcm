<?php

namespace App\Domains\Mobility\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilityAssignmentVersion extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'hcm_mobility_assignment_versions';

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'version_number',
        'version_reason',
        'snapshot_payload',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'snapshot_payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MobilityAssignment::class, 'assignment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
