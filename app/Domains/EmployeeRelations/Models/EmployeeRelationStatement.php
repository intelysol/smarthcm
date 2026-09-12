<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationStatement extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_statements';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'participant_id',
        'statement_type',
        'content',
        'submitted_at',
        'verified_at',
        'verified_by',
        'is_confidential',
        'current_version',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'is_confidential' => 'boolean',
            'current_version' => 'integer',
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

    public function participant(): BelongsTo
    {
        return $this->belongsTo(EmployeeRelationCaseParticipant::class, 'participant_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EmployeeRelationStatementVersion::class, 'statement_id');
    }
}
