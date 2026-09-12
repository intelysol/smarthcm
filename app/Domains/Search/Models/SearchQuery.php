<?php
namespace App\Domains\Search\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class SearchQuery extends Model
{
    use HasUuids;
    protected $table = 'search_queries';
    protected $fillable = ['tenant_id', 'user_id', 'query', 'filters', 'result_count', 'duration_ms', 'selected_entry_id'];
    protected function casts(): array { return ['filters' => 'array']; }
}
