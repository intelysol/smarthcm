<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRestructure extends Model
{
    use HasUuids;

    protected $table = 'loan_restructures';

    protected $fillable = [
        'tenant_id',
        'loan_application_id',
        'restructure_type',
        'previous_schedule_version',
        'new_schedule_version',
        'previous_balance',
        'new_balance',
        'reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'previous_schedule_version' => 'integer',
        'new_schedule_version' => 'integer',
        'previous_balance' => 'decimal:4',
        'new_balance' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
