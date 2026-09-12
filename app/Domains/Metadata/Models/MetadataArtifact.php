<?php

namespace App\Domains\Metadata\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataArtifact extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'metadata_artifacts';

    protected $fillable = ['tenant_id', 'scope_type', 'scope_id', 'artifact_type', 'key', 'name', 'description', 'status', 'version', 'definition', 'created_by', 'updated_by', 'reviewed_by', 'approved_at', 'effective_from', 'effective_to'];

    protected function casts(): array
    {
        return ['definition' => 'array', 'approved_at' => 'datetime', 'effective_from' => 'datetime', 'effective_to' => 'datetime'];
    }
}
