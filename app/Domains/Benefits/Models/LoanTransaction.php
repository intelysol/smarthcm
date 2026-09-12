<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanTransaction extends Model
{
    use HasUuids;

    protected $table = 'loan_transactions';

    protected $fillable = [
        'tenant_id',
        'loan_application_id',
        'employee_id',
        'transaction_type',
        'transaction_date',
        'amount',
        'principal_portion',
        'interest_portion',
        'running_balance',
        'source_reference_type',
        'source_reference_id',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:4',
        'principal_portion' => 'decimal:4',
        'interest_portion' => 'decimal:4',
        'running_balance' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
