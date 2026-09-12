<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpensePolicy extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'expense_policies';

    protected $fillable = [
        'tenant_id',
        'policy_code',
        'name',
        'description',
        'effective_from',
        'effective_to',
        'current_version',
        'status',
        'assignment_rules',
        'rules',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'current_version' => 'integer',
        'assignment_rules' => 'array',
        'rules' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ExpensePolicyVersion::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ExpensePolicyAssignment::class);
    }
}
