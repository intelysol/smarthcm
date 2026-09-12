<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanAgreement extends Model
{
    use HasUuids;

    protected $table = 'loan_agreements';

    protected $fillable = [
        'tenant_id',
        'loan_application_id',
        'agreement_number',
        'principal_amount',
        'interest_rate',
        'monthly_installment',
        'tenure_months',
        'repayment_start_date',
        'repayment_end_date',
        'terms_and_conditions',
        'document_reference',
        'signed_at',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:4',
        'interest_rate' => 'decimal:4',
        'monthly_installment' => 'decimal:4',
        'tenure_months' => 'integer',
        'repayment_start_date' => 'date',
        'repayment_end_date' => 'date',
        'signed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }
}
