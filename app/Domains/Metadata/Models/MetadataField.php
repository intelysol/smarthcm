<?php

namespace App\Domains\Metadata\Models;

use Database\Factories\Domains\Metadata\MetadataFieldFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataField extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'metadata_fields';

    protected $fillable = ['entity_id', 'key', 'label', 'field_type', 'sort_order', 'configuration', 'validation_rules', 'visibility_expression', 'formula', 'row_version', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['configuration' => 'array', 'validation_rules' => 'array', 'visibility_expression' => 'array', 'formula' => 'array'];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(MetadataEntity::class, 'entity_id');
    }

    protected static function newFactory(): MetadataFieldFactory
    {
        return MetadataFieldFactory::new();
    }
}
