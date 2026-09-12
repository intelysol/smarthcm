<?php

namespace App\Domains\Metadata\Models;

use App\Models\User;
use Database\Factories\Domains\Metadata\MetadataEntityFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetadataEntity extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'metadata_entities';

    protected $fillable = ['tenant_id', 'key', 'label', 'module', 'entity_type', 'category', 'icon', 'color', 'description', 'status', 'version', 'settings', 'supports_soft_deletes', 'supports_audit', 'row_version', 'created_by', 'updated_by', 'deleted_by'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'supports_soft_deletes' => 'boolean', 'supports_audit' => 'boolean'];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(MetadataField::class, 'entity_id')->orderBy('sort_order');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(MetadataForm::class, 'entity_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(MetadataRecord::class, 'entity_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function newFactory(): MetadataEntityFactory
    {
        return MetadataEntityFactory::new();
    }
}
