<?php

namespace App\Domains\EmployeeProfile\Http\Controllers;

use App\Domains\EmployeeProfile\Services\EmployeeDirectoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDirectoryController extends Controller
{
    public function __construct(protected EmployeeDirectoryService $directoryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'department_id',
            'branch_id',
            'work_location_id',
            'reporting_manager_id',
            'status',
        ]);

        $perPage = (int) $request->input('per_page', 15);
        $paginator = $this->directoryService->searchDirectory($request->user(), $filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
