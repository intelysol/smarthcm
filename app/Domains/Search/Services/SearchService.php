<?php
namespace App\Domains\Search\Services;
use App\Domains\Search\Models\{SavedSearch, SearchEntry, SearchQuery};
use App\Models\User;
use Illuminate\Support\Str;
class SearchService
{
    public function search(string $tenantId, User $user, string $term, array $filters = []): array
    {
        $started = hrtime(true); $needle = trim($term); $query = SearchEntry::query()->where('tenant_id', $tenantId);
        $query->when($needle !== '', fn ($q) => $q->where(fn ($inner) => $inner->whereLike('title', "%{$needle}%")->orWhereLike('content', "%{$needle}%")));
        foreach (['module', 'entity_type', 'status', 'owner_id'] as $field) { if (isset($filters[$field])) $query->where($field, $filters[$field]); }
        if (! empty($filters['tag'])) $query->whereJsonContains('tags', $filters['tag']);
        $rows = $query->get()->map(function (SearchEntry $entry) use ($needle): array { $title = strtolower($entry->title); $score = $needle !== '' && $title === strtolower($needle) ? 100 : (str_contains($title, strtolower($needle)) ? 50 : 10); return [...$entry->toArray(), 'score' => $score, 'snippet' => Str::limit($entry->content ?? '', 180)]; })->sortByDesc('score')->values()->all();
        SearchQuery::query()->create(['tenant_id' => $tenantId, 'user_id' => $user->id, 'query' => $term, 'filters' => $filters, 'result_count' => count($rows), 'duration_ms' => (int) ((hrtime(true) - $started) / 1_000_000)]);
        return $rows;
    }
    public function suggest(string $tenantId, string $term): array { return SearchEntry::query()->where('tenant_id', $tenantId)->where('title', 'like', trim($term).'%')->orderBy('title')->limit(10)->pluck('title')->unique()->values()->all(); }
    public function index(string $tenantId, array $record): SearchEntry { return SearchEntry::query()->updateOrCreate(['tenant_id' => $tenantId, 'module' => $record['module'], 'entity_type' => $record['entity_type'], 'entity_id' => (string) $record['entity_id']], [...$record, 'tenant_id' => $tenantId, 'indexed_at' => now()]); }
}
