<?php

namespace App\Domains\EmployeeProfile\Http\Controllers;

use App\Domains\EmployeeProfile\Services\PeopleSearchService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleSearchController extends Controller
{
    public function __construct(protected PeopleSearchService $searchService)
    {
    }

    public function search(Request $request): JsonResponse
    {
        $term = (string) $request->input('q', '');
        $limit = (int) $request->input('limit', 10);
        $results = $this->searchService->search($request->user(), $term, $limit);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $term = (string) $request->input('q', '');
        $limit = (int) $request->input('limit', 5);
        $results = $this->searchService->autocomplete($request->user(), $term, $limit);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
}
