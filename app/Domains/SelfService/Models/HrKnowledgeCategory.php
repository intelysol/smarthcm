<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrKnowledgeCategory extends Model
{
    use HasUuids;

    protected $table = 'hr_knowledge_categories';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'icon',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(HrKnowledgeArticle::class, 'hr_knowledge_category_id');
    }
}
