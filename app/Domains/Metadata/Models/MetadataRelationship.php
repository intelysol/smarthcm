<?php

namespace App\Domains\Metadata\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataRelationship extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'metadata_relationships';

    protected $fillable = ['source_entity_id', 'target_entity_id', 'key', 'relationship_type', 'source_field', 'target_field', 'configuration', 'created_by'];

    protected function casts(): array
    {
        return ['configuration' => 'array'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(MetadataEntity::class, 'source_entity_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(MetadataEntity::class, 'target_entity_id');
    }
}
