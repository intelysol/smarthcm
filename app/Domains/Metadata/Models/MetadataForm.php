<?php

namespace App\Domains\Metadata\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataForm extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'metadata_forms';

    protected $fillable = ['entity_id', 'key', 'name', 'status', 'layout', 'actions', 'version', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['layout' => 'array', 'actions' => 'array'];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(MetadataEntity::class, 'entity_id');
    }
}
