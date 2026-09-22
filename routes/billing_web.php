<?php

declare(strict_types=1);

use App\Domains\Billing\Http\Controllers\BillingWebController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Platform Operations Billing Command Center
    Route::prefix('operations/billing')->group(function () {
        Route::get('/', [BillingWebController::class, 'adminOverview'])->name('operations.billing.overview');
    });

    // Tenant Self-Service Billing Portal
    Route::prefix('portal/billing')->group(function () {
        Route::get('/', [BillingWebController::class, 'tenantPortal'])->name('portal.billing.index');
        Route::post('/plan', [BillingWebController::class, 'tenantChangePlan'])->name('portal.billing.change-plan');
        Route::post('/invoices/{id}/pay', [BillingWebController::class, 'tenantPayInvoice'])->name('portal.billing.pay-invoice');
        Route::get('/invoices/{id}', [BillingWebController::class, 'tenantDownloadInvoice'])->name('portal.billing.invoice-receipt');
    });
});
