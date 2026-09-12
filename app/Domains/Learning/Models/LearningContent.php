<?php

namespace App\Domains\Learning\Models;

use App\Domains\Documents\Models\Document;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'content_type', 'title', 'body', 'external_url', 'document_id', 'metadata'])]
class LearningContent extends LearningModel
{
    protected $table = 'learning_content';

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Document, LearningContent> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /** @return HasMany<LearningItem> */
    public function items(): HasMany
    {
        return $this->hasMany(LearningItem::class, 'content_id');
    }
}
