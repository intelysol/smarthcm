<?php

declare(strict_types=1);

namespace App\Domains\Operations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataLifecycleJob extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'data_lifecycle_jobs';

    protected $fillable = [
        'tenant_id',
        'job_type',
        'target_class',
        'status',
        'total_records',
        'processed_records',
        'skipped_records',
        'protected_records',
        'failure_reason',
        'checkpoint_token',
        'metadata',
        'initiated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_records' => 'integer',
            'processed_records' => 'integer',
            'skipped_records' => 'integer',
            'protected_records' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
