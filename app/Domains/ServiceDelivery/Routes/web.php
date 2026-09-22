<?php

use App\Domains\ServiceDelivery\Http\Controllers\HrServiceDeliveryWebController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal/hr-services')->middleware(['web'])->group(function () {
    Route::get('/', [HrServiceDeliveryWebController::class, 'commandCenter'])->name('portal.hr-services.command-center');
    Route::get('/cases', [HrServiceDeliveryWebController::class, 'cases'])->name('portal.hr-services.cases');
    Route::get('/cases/{id}', [HrServiceDeliveryWebController::class, 'caseDetail'])->name('portal.hr-services.cases.detail');
    Route::get('/catalog', [HrServiceDeliveryWebController::class, 'catalog'])->name('portal.hr-services.catalog');
    Route::get('/knowledge', [HrServiceDeliveryWebController::class, 'knowledge'])->name('portal.hr-services.knowledge');
});
