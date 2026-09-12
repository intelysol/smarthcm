<?php

namespace App\Domains\Payroll\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPaymentBatch extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'payroll_payment_batches';

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'batch_number',
        'payment_method',
        'bank_format',
        'currency',
        'total_records',
        'total_amount',
        'status',
        'export_file_path',
        'generated_at',
        'submitted_at',
        'paid_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_records' => 'integer',
        'total_amount' => 'decimal:4',
        'generated_at' => 'datetime',
        'submitted_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollPaymentLine::class);
    }
}
