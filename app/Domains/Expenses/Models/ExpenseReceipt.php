<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseReceipt extends Model
{
    use HasUuids;

    protected $table = 'expense_receipts';

    protected $fillable = [
        'tenant_id',
        'expense_claim_line_id',
        'employee_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'receipt_hash',
        'merchant_extracted',
        'invoice_number',
        'extracted_amount',
        'extracted_currency',
        'extracted_tax',
        'ocr_payload',
        'is_verified_by_employee',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'extracted_amount' => 'decimal:4',
        'extracted_tax' => 'decimal:4',
        'ocr_payload' => 'array',
        'is_verified_by_employee' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function claimLine(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaimLine::class, 'expense_claim_line_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
