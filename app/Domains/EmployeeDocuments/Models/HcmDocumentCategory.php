<?php

namespace App\Domains\EmployeeDocuments\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HcmDocumentCategory extends Model
{
    use HasUuids;

    protected $table = 'hcm_document_categories';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function documentTypes(): HasMany
    {
        return $this->hasMany(HcmDocumentType::class, 'category_id');
    }
}
