<?php
namespace App\Domains\Search\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class SearchEntry extends Model
{
    use HasUuids;
    protected $table = 'search_entries';
    protected $fillable = ['tenant_id', 'module', 'entity_type', 'entity_id', 'title', 'content', 'metadata', 'tags', 'status', 'owner_id', 'url', 'indexed_at', 'index_version'];
    protected function casts(): array { return ['metadata' => 'array', 'tags' => 'array', 'indexed_at' => 'datetime']; }
}
