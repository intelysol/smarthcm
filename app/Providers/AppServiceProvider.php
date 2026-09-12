<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Domains\WorkforceProductivity\Contracts\ProductivityCalculationInterface::class,
            \App\Domains\WorkforceProductivity\Services\ProductivityCalculationService::class
        );
        $this->app->bind(
            \App\Domains\WorkforceProductivity\Contracts\WorkforceRoiEvaluatorInterface::class,
            \App\Domains\WorkforceProductivity\Services\WorkforceROIService::class
        );
        $this->app->bind(
            \App\Domains\WorkforceOptimization\Contracts\OptimizationSolverInterface::class,
            \App\Domains\WorkforceOptimization\Services\OptimizationSolverService::class
        );
        $this->app->bind(
            \App\Domains\WorkforceOptimization\Contracts\WorkforceOptimizationInterface::class,
            \App\Domains\WorkforceOptimization\Services\WorkforceOptimizationService::class
        );
        $this->app->bind(
            \App\Domains\WorkforceIntelligence\Contracts\WorkforceCommandCenterInterface::class,
            \App\Domains\WorkforceIntelligence\Services\WorkforceCommandCenterService::class
        );
        $this->app->bind(
            \App\Domains\WorkforceIntelligence\Contracts\KpiOrchestrationInterface::class,
            \App\Domains\WorkforceIntelligence\Services\KpiOrchestrationService::class
        );
        $this->app->bind(
            \App\Domains\WorkforceIntelligence\Contracts\WorkforceIntelligenceAiInterface::class,
            \App\Domains\WorkforceIntelligence\Services\WorkforceIntelligenceAiService::class
        );
        $this->app->bind(
            \App\Domains\WorkforceGovernance\Contracts\WorkforceDataGovernanceInterface::class,
            \App\Domains\WorkforceGovernance\Services\WorkforceDataGovernanceService::class
        );
        $this->app->bind(
            \App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface::class,
            \App\Domains\EmployeeAi\Services\EmployeeAiConciergeService::class
        );
        $this->app->bind(
            \App\Domains\ResponsibleAi\Contracts\ResponsibleAiGovernanceInterface::class,
            \App\Domains\ResponsibleAi\Services\ResponsibleAiGovernanceService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request): Limit {
            return Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}
