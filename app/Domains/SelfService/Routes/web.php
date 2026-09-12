<?php

use App\Domains\SelfService\Http\Controllers\AnnouncementController;
use App\Domains\SelfService\Http\Controllers\EmployeePortalController;
use App\Domains\SelfService\Http\Controllers\HrAgentWorkspaceController;
use App\Domains\SelfService\Http\Controllers\KnowledgeBaseController;
use App\Domains\SelfService\Http\Controllers\ManagerPortalController;
use App\Domains\SelfService\Http\Controllers\ServiceCatalogController;
use App\Domains\SelfService\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('self-service')->middleware(['web'])->group(function () {
    Route::get('/', [EmployeePortalController::class, 'dashboard'])->name('self-service.dashboard');
    Route::get('/catalog', [ServiceCatalogController::class, 'index'])->name('self-service.catalog.index');
    Route::get('/catalog/{service}', [ServiceCatalogController::class, 'show'])->name('self-service.catalog.show');
    Route::get('/requests', [ServiceRequestController::class, 'index'])->name('self-service.requests.index');
    Route::get('/requests/{hrServiceRequest}', [ServiceRequestController::class, 'show'])->name('self-service.requests.show');
    Route::get('/manager', [ManagerPortalController::class, 'dashboard'])->name('self-service.manager.dashboard');
    Route::get('/agent/workspace', [HrAgentWorkspaceController::class, 'index'])->name('self-service.agent.workspace');
    Route::get('/knowledge', [KnowledgeBaseController::class, 'index'])->name('self-service.knowledge.index');
    Route::get('/knowledge/{article}', [KnowledgeBaseController::class, 'show'])->name('self-service.knowledge.show');
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('self-service.announcements.index');
});
