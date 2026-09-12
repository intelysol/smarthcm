<?php

namespace App\Domains\Benefits\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanSchedule extends Model
{
    use HasUuids;

    protected $table = 'loan_schedules';

    protected $fillable = [
        'tenant_id',
        'loan_application_id',
        'schedule_version',
        'total_principal',
        'total_interest',
        'total_payable',
        'total_paid',
        'remaining_balance',
        'is_active',
    ];

    protected $casts = [
        'schedule_version' => 'integer',
        'total_principal' => 'decimal:4',
        'total_interest' => 'decimal:4',
        'total_payable' => 'decimal:4',
        'total_paid' => 'decimal:4',
        'remaining_balance' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class)->orderBy('installment_number');
    }
}
