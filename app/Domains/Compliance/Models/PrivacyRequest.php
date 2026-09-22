<?php

declare(strict_types=1);

namespace App\Domains\Compliance\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyRequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'privacy_requests';

    protected $fillable = [
        'tenant_id',
        'request_reference',
        'user_id',
        'request_type',
        'identity_verified',
        'status',
        'blocked_reason',
        'export_path',
        'export_hash_sha256',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'identity_verified' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
