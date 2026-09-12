<?php

namespace App\Domains\Lifecycle\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelActionBulkBatch extends Model
{
    use HasUuids;

    protected $table = 'personnel_action_bulk_batches';

    protected $fillable = [
        'tenant_id',
        'action_type_id',
        'created_by',
        'name',
        'status',
        'total_items',
        'valid_items',
        'warning_items',
        'error_items',
        'processed_items',
        'successful_items',
        'failed_items',
        'effective_date',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'valid_items' => 'integer',
        'warning_items' => 'integer',
        'error_items' => 'integer',
        'processed_items' => 'integer',
        'successful_items' => 'integer',
        'failed_items' => 'integer',
        'effective_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function actionType(): BelongsTo
    {
        return $this->belongsTo(PersonnelActionType::class, 'action_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PersonnelActionBulkItem::class, 'batch_id');
    }
}
