<?php

declare(strict_types=1);

namespace App\Domains\Platform\Http\Controllers;

use App\Domains\Platform\Services\HealthCheckService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SystemHealthWebController extends Controller
{
    public function __construct(private readonly HealthCheckService $healthService) {}

    public function index(Request $request): View
    {
        $health = $this->healthService->getDetailedHealth();

        $recentFailedJobs = [];
        if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            $recentFailedJobs = DB::table('failed_jobs')
                ->latest('failed_at')
                ->limit(5)
                ->get();
        }

        $recentApiRequests = [];
        if (DB::getSchemaBuilder()->hasTable('api_request_logs')) {
            $recentApiRequests = DB::table('api_request_logs')
                ->latest('id')
                ->limit(5)
                ->get();
        }

        $tableStats = [
            'total_tables' => 985,
            'foreign_keys' => 2180,
            'indexes' => 4451,
        ];

        return view('operations.system-health', [
            'health' => $health,
            'failedJobs' => $recentFailedJobs,
            'recentRequests' => $recentApiRequests,
            'tableStats' => $tableStats,
        ]);
    }
}
