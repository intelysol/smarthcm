<?php
namespace App\Domains\Search\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class SavedSearch extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'user_id', 'name', 'query', 'filters', 'is_pinned', 'is_shared', 'schedule'];
    protected function casts(): array { return ['filters' => 'array', 'is_pinned' => 'boolean', 'is_shared' => 'boolean']; }
}
