<?php

use App\Domains\Platform\Services\OperationalAlertService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// -----------------------------------------------------------------------------
// Enterprise Platform Scheduled Operations & Health Monitoring
// -----------------------------------------------------------------------------

// 1. Operational Alert Evaluation (Every 10 minutes)
Schedule::call(function () {
    app(OperationalAlertService::class)->evaluateAndAlert();
})->everyTenMinutes()->name('evaluate-operational-alerts')->withoutOverlapping();

// 2. Attendance Daily Processing (Daily at 01:00 UTC)
Schedule::command('hcm:attendance:daily-process')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();

// 3. Workforce Schedule Compliance Monitoring (Every 15 minutes)
Schedule::command('hcm:schedule:monitor')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// 4. Time Compliance & Overtime Check (Daily at 02:00 UTC)
Schedule::command('hcm:time-compliance:monitor')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// 5. Service Delivery SLA Evaluation (Every 30 minutes)
Schedule::command('hcm:service-sla:evaluate')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// 6. Analytics Data Quality Snapshot (Daily at 03:00 UTC)
Schedule::command('hcm:analytics:data-quality')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();

// 7. Cleanup & Maintenance (Weekly on Sunday)
Schedule::command('queue:prune-failed', ['--hours' => 168])
    ->weeklyOn(0, '04:00')
    ->withoutOverlapping();

// -----------------------------------------------------------------------------
// Commercial SaaS Billing Automations
// -----------------------------------------------------------------------------

// 8. Daily Subscription Renewal & Automated Invoicing (Daily at 01:30 UTC)
Schedule::call(function () {
    $lifecycleService = app(\Flow\Packages\Billing\Services\SubscriptionLifecycleService::class);
    $invoiceEngine = app(\Flow\Packages\Billing\Services\InvoiceEngine::class);

    $dueForRenewal = \Flow\Packages\Billing\Domain\Models\BillingSubscription::where('status', 'active')
        ->where('auto_renew', true)
        ->where('current_cycle_end', '<=', now()->addDay())
        ->get();

    foreach ($dueForRenewal as $sub) {
        $lifecycleService->renew($sub);
        $invoiceEngine->generateSubscriptionInvoice($sub);
    }
})->dailyAt('01:30')->name('billing-renewals-and-invoicing')->withoutOverlapping();

// 9. Hourly Usage Aggregation & Alerting (Hourly)
Schedule::call(function () {
    $usageService = app(\Flow\Packages\Billing\Services\UsageMeteringService::class);
    $activeSubs = \Flow\Packages\Billing\Domain\Models\BillingSubscription::whereIn('status', ['active', 'trialing'])->get();

    $startOfMonth = now()->startOfMonth();
    $endOfMonth = now()->endOfMonth();

    foreach ($activeSubs as $sub) {
        $usageService->snapshotPeriod($sub->tenant_id, 'api_calls', $startOfMonth, $endOfMonth);
        $usageService->snapshotPeriod($sub->tenant_id, 'ai_tokens', $startOfMonth, $endOfMonth);
        $usageService->evaluateThreshold($sub->tenant_id, 'active_employees', 'employee_limit');
    }
})->hourly()->name('billing-usage-aggregation')->withoutOverlapping();

// 10. Daily Commercial Reconciliation Audit (Daily at 03:30 UTC)
Schedule::call(function () {
    app(\Flow\Packages\Billing\Services\BillingReconciliationService::class)->auditDaily();
})->dailyAt('03:30')->name('billing-reconciliation-audit')->withoutOverlapping();

// Artisan commands for manual execution and testing
Artisan::command('billing:renewals:process', function () {
    $this->info('Processing commercial subscription renewals...');
    $lifecycleService = app(\Flow\Packages\Billing\Services\SubscriptionLifecycleService::class);
    $invoiceEngine = app(\Flow\Packages\Billing\Services\InvoiceEngine::class);

    $subs = \Flow\Packages\Billing\Domain\Models\BillingSubscription::where('status', 'active')->get();
    $renewed = 0;
    foreach ($subs as $s) {
        $lifecycleService->renew($s);
        $invoiceEngine->generateSubscriptionInvoice($s);
        $renewed++;
    }
    $this->info("Renewed {$renewed} subscriptions and generated invoices.");
})->purpose('Process subscription renewals and generate invoices');

Artisan::command('billing:reconciliation:audit', function () {
    $this->info('Executing commercial reconciliation audit...');
    $res = app(\Flow\Packages\Billing\Services\BillingReconciliationService::class)->auditDaily();
    $this->info("Audited {$res['total_audited']} invoices. Discrepancies found: {$res['discrepancies_found']}.");
})->purpose('Run daily commercial billing reconciliation audit');
