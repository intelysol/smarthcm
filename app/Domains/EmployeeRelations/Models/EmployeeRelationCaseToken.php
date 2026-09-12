<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelationCaseToken extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_case_tokens';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'token_hash',
        'expires_at',
        'status',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCase::class, 'case_id');
    }

    public function isValid(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }
}
