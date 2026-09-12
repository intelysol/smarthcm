<?php

namespace App\Domains\Expenses\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseAccountingExport extends Model
{
    use HasUuids;

    protected $table = 'expense_accounting_exports';

    protected $fillable = [
        'tenant_id',
        'batch_number',
        'export_date',
        'total_amount',
        'currency',
        'period_start',
        'period_end',
        'status',
        'gl_export_payload',
        'exported_by',
        'exported_at',
    ];

    protected $casts = [
        'export_date' => 'date',
        'total_amount' => 'decimal:4',
        'period_start' => 'date',
        'period_end' => 'date',
        'gl_export_payload' => 'array',
        'exported_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}
