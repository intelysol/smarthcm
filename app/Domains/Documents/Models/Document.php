<?php

namespace App\Domains\Documents\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['tenant_id', 'category_id', 'folder_id', 'title', 'description', 'tags', 'module', 'related_type', 'related_id', 'status', 'classification', 'retention_policy', 'legal_hold', 'owner_id', 'current_version'];
    protected function casts(): array { return ['tags' => 'array', 'legal_hold' => 'boolean']; }
    public function versions(): HasMany { return $this->hasMany(DocumentVersion::class); }
    public function audits(): HasMany { return $this->hasMany(DocumentAudit::class); }
}
