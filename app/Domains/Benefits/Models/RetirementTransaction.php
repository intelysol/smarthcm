<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetirementTransaction extends Model
{
    use HasUuids;

    protected $table = 'retirement_transactions';

    protected $fillable = [
        'tenant_id',
        'retirement_account_id',
        'employee_id',
        'transaction_type',
        'transaction_date',
        'amount',
        'running_balance',
        'currency',
        'source_reference_type',
        'source_reference_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:4',
        'running_balance' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(RetirementAccount::class, 'retirement_account_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
