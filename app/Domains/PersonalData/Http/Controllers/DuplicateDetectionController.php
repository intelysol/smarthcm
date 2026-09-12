<?php

namespace App\Domains\PersonalData\Http\Controllers;

use App\Domains\PersonalData\Services\DuplicateDetectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DuplicateDetectionController extends Controller
{
    public function __construct(
        protected DuplicateDetectionService $duplicateService
    ) {}

    /**
     * Get advisory duplicate employee candidates.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->query('tenant_id');
        $employeeId = $request->query('employee_id');

        $duplicates = $this->duplicateService->detectDuplicates($tenantId, $employeeId);

        return response()->json([
            'status' => 'success',
            'data' => $duplicates,
            'meta' => [
                'is_advisory' => true,
                'automated_merges_prohibited' => true,
            ],
        ]);
    }
}
