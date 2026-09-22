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
        $this->app->bind(
            \App\Domains\AiOperations\Contracts\AiOperationsInterface::class,
            \App\Domains\AiOperations\Services\AiOperationsService::class
        );
        $this->app->bind(
            \App\Domains\TenantAdmin\Contracts\TenantAdministrationInterface::class,
            \App\Domains\TenantAdmin\Services\TenantAdministrationService::class
        );
        $this->app->singleton(\App\Domains\Shared\Services\FormattingService::class);
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

        // Epic 2.70 Centralized Formatting Directives
        \Illuminate\Support\Facades\Blade::directive('formatDate', function ($expression) {
            return "<?php echo app(\App\Domains\Shared\Services\FormattingService::class)->formatDate({$expression}); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo app(\App\Domains\Shared\Services\FormattingService::class)->formatDateTime({$expression}); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('formatCurrency', function ($expression) {
            return "<?php echo app(\App\Domains\Shared\Services\FormattingService::class)->formatCurrency({$expression}); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('formatNumber', function ($expression) {
            return "<?php echo app(\App\Domains\Shared\Services\FormattingService::class)->formatNumber({$expression}); ?>";
        });
    }
}
