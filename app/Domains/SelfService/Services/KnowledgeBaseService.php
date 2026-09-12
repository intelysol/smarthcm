<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeCategory;
use App\Domains\SelfService\Models\HrKnowledgeFeedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class KnowledgeBaseService
{
    public function getCategories(string $tenantId): Collection
    {
        return HrKnowledgeCategory::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('display_order', 'asc')
            ->get();
    }

    public function searchArticles(string $tenantId, string $query): Collection
    {
        return HrKnowledgeArticle::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->with(['category'])
            ->get();
    }

    public function getDeflectionSuggestions(string $tenantId, ?string $serviceDefinitionId = null, ?string $query = null): Collection
    {
        $builder = HrKnowledgeArticle::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published');

        if ($serviceDefinitionId) {
            $builder->where(function ($q) use ($serviceDefinitionId, $query) {
                $q->where('hr_service_definition_id', $serviceDefinitionId);
                if ($query) {
                    $q->orWhere('title', 'like', "%{$query}%")
                      ->orWhere('summary', 'like', "%{$query}%");
                }
            });
        } elseif ($query) {
            $builder->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%");
            });
        }

        return $builder->limit(4)->get();
    }

    public function recordFeedback(HrKnowledgeArticle $article, ?Employee $employee, bool $isHelpful, ?string $comments = null): HrKnowledgeFeedback
    {
        $feedback = HrKnowledgeFeedback::create([
            'tenant_id' => $article->tenant_id,
            'hr_knowledge_article_id' => $article->id,
            'employee_id' => $employee?->id,
            'is_helpful' => $isHelpful,
            'comments' => $comments,
        ]);

        if ($isHelpful) {
            $article->increment('helpful_count');
        } else {
            $article->increment('not_helpful_count');
        }

        return $feedback;
    }
}
