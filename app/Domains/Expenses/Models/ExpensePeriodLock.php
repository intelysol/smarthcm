<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePeriodLock extends Model
{
    use HasUuids;

    protected $table = 'expense_period_locks';

    protected $fillable = [
        'tenant_id',
        'period_name',
        'start_date',
        'end_date',
        'is_locked',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
