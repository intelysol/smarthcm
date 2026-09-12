<?php

namespace App\Domains\Metadata\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataRecord extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'metadata_records';

    protected $fillable = ['tenant_id', 'entity_id', 'data', 'status', 'created_by', 'updated_by', 'row_version'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(MetadataEntity::class, 'entity_id');
    }
}
