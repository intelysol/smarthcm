<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationCaseParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_case_participants';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'participant_type',
        'employee_id',
        'user_id',
        'name',
        'email',
        'role_title',
        'is_anonymous',
        'access_restricted',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'access_restricted' => 'boolean',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(EmployeeRelationStatement::class, 'participant_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(EmployeeRelationInterview::class, 'participant_id');
    }
}
