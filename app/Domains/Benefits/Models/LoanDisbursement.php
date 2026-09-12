<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanDisbursement extends Model
{
    use HasUuids;

    protected $table = 'loan_disbursements';

    protected $fillable = [
        'tenant_id',
        'loan_application_id',
        'disbursed_amount',
        'disbursement_date',
        'disbursement_method',
        'transaction_reference',
        'bank_account_info',
        'disbursed_by',
    ];

    protected $casts = [
        'disbursed_amount' => 'decimal:4',
        'disbursement_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function disburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
