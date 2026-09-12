<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAccountingExport extends Model
{
    use HasUuids;

    protected $table = 'payroll_accounting_exports';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'journal_voucher_number',
        'entry_date',
        'currency',
        'total_debits',
        'total_credits',
        'distribution_lines',
        'status',
        'exported_at',
        'exported_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debits' => 'decimal:4',
        'total_credits' => 'decimal:4',
        'distribution_lines' => 'array',
        'exported_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}
