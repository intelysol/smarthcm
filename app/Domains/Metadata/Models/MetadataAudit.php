<?php

namespace App\Domains\Metadata\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MetadataAudit extends Model
{
    use HasUuids;

    protected $table = 'metadata_audits';

    protected $fillable = ['tenant_id', 'subject_type', 'subject_id', 'user_id', 'action', 'old_values', 'new_values', 'occurred_at'];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'occurred_at' => 'datetime'];
    }
}
