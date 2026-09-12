<?php

namespace App\Domains\Offboarding\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeparationHandoverItem extends Model
{
    use HasUuids;

    protected $table = 'separation_handover_items';

    protected $fillable = [
        'tenant_id',
        'separation_handover_record_id',
        'title',
        'category',
        'status',
        'notes',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(SeparationHandoverRecord::class, 'separation_handover_record_id');
    }
}
