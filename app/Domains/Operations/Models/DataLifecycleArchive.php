<?php

declare(strict_types=1);

namespace App\Domains\Operations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataLifecycleArchive extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'data_lifecycle_archives';

    protected $fillable = [
        'tenant_id',
        'data_class',
        'original_table',
        'record_count',
        'file_count',
        'checksum_sha256',
        'storage_path',
        'manifest',
        'retention_until',
        'status',
        'version',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'record_count' => 'integer',
            'file_count' => 'integer',
            'manifest' => 'array',
            'retention_until' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
