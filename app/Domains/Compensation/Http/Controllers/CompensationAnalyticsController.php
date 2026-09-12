<?php

declare(strict_types=1);

namespace App\Domains\Compensation\Http\Controllers;

use App\Domains\Compensation\Models\CompensationCycle;
use App\Domains\Compensation\Services\PayEquityAnalyticsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CompensationAnalyticsController extends Controller
{
    public function __construct(
        protected PayEquityAnalyticsService $equityService
    ) {}

    public function payEquity(CompensationCycle $cycle): JsonResponse
    {
        $metrics = $this->equityService->analyzeCycleEquity($cycle);

        return response()->json(['data' => $metrics]);
    }
}
