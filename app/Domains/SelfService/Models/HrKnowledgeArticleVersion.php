<?php

namespace App\Domains\SelfService\Models;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrKnowledgeArticleVersion extends Model
{
    use HasUuids;

    protected $table = 'hr_knowledge_article_versions';

    protected $fillable = [
        'tenant_id',
        'hr_knowledge_article_id',
        'version_number',
        'title',
        'content',
        'change_notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(HrKnowledgeArticle::class, 'hr_knowledge_article_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
