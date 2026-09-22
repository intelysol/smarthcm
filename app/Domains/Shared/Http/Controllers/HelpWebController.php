<?php

declare(strict_types=1);

namespace App\Domains\Shared\Http\Controllers;

use App\Domains\Shared\Services\HelpCenterService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpWebController extends Controller
{
    public function __construct(
        protected HelpCenterService $helpService
    ) {}

    public function index(Request $request): View
    {
        $categories = $this->helpService->getCategories();
        $query = $request->query('q', '');

        if ($query !== '') {
            $articles = $this->helpService->search((string) $query);
        } else {
            $articles = $this->helpService->getAllArticles();
        }

        return view('help.index', [
            'categories' => $categories,
            'articles' => $articles,
            'query' => $query,
        ]);
    }

    public function category(Request $request, string $category): View
    {
        $categories = $this->helpService->getCategories();

        if (!isset($categories[$category])) {
            abort(404, "Help category '{$category}' not found.");
        }

        $categoryData = $categories[$category];
        $articles = $this->helpService->getArticlesByCategory($category);

        return view('help.category', [
            'category' => $categoryData,
            'articles' => $articles,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $article = $this->helpService->getArticleBySlug($slug);

        if (!$article) {
            abort(404, "Help article '{$slug}' not found.");
        }

        $categories = $this->helpService->getCategories();
        $categoryData = $categories[$article['category']] ?? [
            'key' => $article['category'],
            'title' => ucfirst(str_replace('-', ' ', $article['category'])),
            'icon' => 'fa-solid fa-book',
        ];

        $relatedArticles = array_values(array_filter(
            $this->helpService->getArticlesByCategory($article['category']),
            fn ($a) => $a['slug'] !== $slug
        ));

        return view('help.show', [
            'article' => $article,
            'category' => $categoryData,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
