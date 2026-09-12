<?php

namespace App\Domains\PersonalData\Models;

use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HcmEmployeeBankChangeRequest extends Model
{
    use HasUuids;

    protected $table = 'hcm_employee_bank_change_requests';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'request_number',
        'request_type',
        'bank_name',
        'branch_name',
        'account_title',
        'account_number_encrypted',
        'masked_account_number',
        'iban_encrypted',
        'masked_iban',
        'payment_method',
        'reason',
        'status',
        'payroll_actioned_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'account_number_encrypted' => 'encrypted',
        'iban_encrypted' => 'encrypted',
        'payroll_actioned_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
