<?php

namespace App\Domains\SelfService\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Requests\KnowledgeFeedbackRequest;
use App\Domains\SelfService\Services\KnowledgeBaseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    public function __construct(
        protected KnowledgeBaseService $knowledgeService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $query = $request->input('search');

        if ($query) {
            $articles = $this->knowledgeService->searchArticles($tenantId, $query);
            $categories = collect();
        } else {
            $categories = $this->knowledgeService->getCategories($tenantId);
            $articles = HrKnowledgeArticle::where('tenant_id', $tenantId)->where('status', 'published')->limit(10)->get();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'articles' => $articles,
            ]);
        }

        return view('self-service.knowledge.index', compact('categories', 'articles', 'query'));
    }

    public function show(HrKnowledgeArticle $article): View|JsonResponse
    {
        $article->increment('views_count');
        $article->load(['category', 'service']);

        if (request()->wantsJson()) {
            return response()->json($article);
        }

        return view('self-service.knowledge.show', compact('article'));
    }

    public function feedback(KnowledgeFeedbackRequest $request, HrKnowledgeArticle $article): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee_id ? Employee::find($user->employee_id) : null;

        $feedback = $this->knowledgeService->recordFeedback(
            $article,
            $employee,
            $request->boolean('is_helpful'),
            $request->input('comments')
        );

        return response()->json([
            'message' => 'Feedback submitted.',
            'data' => $feedback,
        ]);
    }
}
