<?php

use App\Domains\ServiceDelivery\Http\Controllers\HrServiceDeliveryApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('hr-services')->group(function () {
    Route::get('dashboard', [HrServiceDeliveryApiController::class, 'dashboard'])->name('api.hr-services.dashboard');
    Route::get('cases', [HrServiceDeliveryApiController::class, 'cases'])->name('api.hr-services.cases');
    Route::get('cases/{id}', [HrServiceDeliveryApiController::class, 'caseDetail'])->name('api.hr-services.cases.detail');
    Route::post('cases/{id}/assign', [HrServiceDeliveryApiController::class, 'assign'])->name('api.hr-services.cases.assign');
    Route::post('cases/{id}/escalate', [HrServiceDeliveryApiController::class, 'escalate'])->name('api.hr-services.cases.escalate');
    Route::post('cases/{id}/resolve', [HrServiceDeliveryApiController::class, 'resolve'])->name('api.hr-services.cases.resolve');
    Route::post('cases/{id}/close', [HrServiceDeliveryApiController::class, 'close'])->name('api.hr-services.cases.close');
    Route::post('cases/{id}/comments', [HrServiceDeliveryApiController::class, 'addComment'])->name('api.hr-services.cases.comments');
    Route::get('cases/{id}/ai-summary', [HrServiceDeliveryApiController::class, 'aiSummary'])->name('api.hr-services.cases.ai-summary');
    Route::post('cases/{id}/feedback', [HrServiceDeliveryApiController::class, 'submitFeedback'])->name('api.hr-services.cases.feedback');

    Route::get('catalog', [HrServiceDeliveryApiController::class, 'catalog'])->name('api.hr-services.catalog');
    Route::post('requests', [HrServiceDeliveryApiController::class, 'submitRequest'])->name('api.hr-services.requests.submit');

    Route::get('knowledge', [HrServiceDeliveryApiController::class, 'knowledge'])->name('api.hr-services.knowledge');
    Route::get('queues', [HrServiceDeliveryApiController::class, 'queues'])->name('api.hr-services.queues');
    Route::get('analytics', [HrServiceDeliveryApiController::class, 'analytics'])->name('api.hr-services.analytics');
});
