<?php

declare(strict_types=1);

use App\Domains\Billing\Http\Controllers\BillingApiController;
use App\Domains\Billing\Http\Controllers\BillingWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/billing')->group(function () {
    // Public Catalog
    Route::get('/products', [BillingApiController::class, 'products'])->name('api.billing.products');
    Route::get('/plans', [BillingApiController::class, 'plans'])->name('api.billing.plans');

    // Tenant Commercial Subscriptions
    Route::get('/subscriptions/current', [BillingApiController::class, 'currentSubscription'])->name('api.billing.subscriptions.current');
    Route::post('/subscriptions/preview-change', [BillingApiController::class, 'previewChange'])->name('api.billing.subscriptions.preview');
    Route::post('/subscriptions/change', [BillingApiController::class, 'changeSubscription'])->name('api.billing.subscriptions.change');

    // Invoices & Payments
    Route::get('/invoices', [BillingApiController::class, 'invoices'])->name('api.billing.invoices');
    Route::get('/invoices/{id}', [BillingApiController::class, 'invoiceDetail'])->name('api.billing.invoices.detail');
    Route::post('/invoices/{id}/pay', [BillingApiController::class, 'payInvoice'])->name('api.billing.invoices.pay');
    Route::post('/invoices/{id}/discount', [BillingApiController::class, 'applyDiscount'])->name('api.billing.invoices.discount');

    // Usage Metering
    Route::post('/usage/record', [BillingApiController::class, 'recordUsage'])->name('api.billing.usage.record');
    Route::get('/usage/summary', [BillingApiController::class, 'usageSummary'])->name('api.billing.usage.summary');

    // Payment Provider Webhooks
    Route::post('/webhooks/{provider}', [BillingWebhookController::class, 'handle'])->name('api.billing.webhooks');
});
