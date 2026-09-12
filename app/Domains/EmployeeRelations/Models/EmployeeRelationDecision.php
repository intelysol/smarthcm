<?php

namespace App\Domains\EmployeeRelations\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRelationDecision extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_relation_decisions';

    protected $fillable = [
        'tenant_id',
        'case_id',
        'decision_maker_id',
        'decision',
        'reason',
        'effective_date',
        'decided_at',
        'is_draft',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'decided_at' => 'datetime',
            'is_draft' => 'boolean',
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

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_maker_id');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(EmployeeRelationCorrectiveAction::class, 'decision_id');
    }
}
