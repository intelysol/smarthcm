<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrKnowledgeArticle extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'hr_knowledge_articles';

    protected $fillable = [
        'tenant_id',
        'hr_knowledge_category_id',
        'hr_service_definition_id',
        'slug',
        'title',
        'summary',
        'content',
        'keywords',
        'audience',
        'status',
        'views_count',
        'helpful_count',
        'not_helpful_count',
        'author_user_id',
        'published_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'views_count' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
        'published_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HrKnowledgeCategory::class, 'hr_knowledge_category_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(HrServiceDefinition::class, 'hr_service_definition_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(HrKnowledgeArticleVersion::class, 'hr_knowledge_article_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(HrKnowledgeFeedback::class, 'hr_knowledge_article_id');
    }
}
