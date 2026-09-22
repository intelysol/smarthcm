<?php

use App\Domains\SelfService\Http\Controllers\AnnouncementController;
use App\Domains\SelfService\Http\Controllers\EmployeePortalController;
use App\Domains\SelfService\Http\Controllers\HrAgentWorkspaceController;
use App\Domains\SelfService\Http\Controllers\HrSharedServicesWorkspaceController;
use App\Domains\SelfService\Http\Controllers\KnowledgeBaseController;
use App\Domains\SelfService\Http\Controllers\ManagerPortalController;
use App\Domains\SelfService\Http\Controllers\ManagerTeamController;
use App\Domains\SelfService\Http\Controllers\NotificationController;
use App\Domains\SelfService\Http\Controllers\OmnichannelIntakeController;
use App\Domains\SelfService\Http\Controllers\PortalDashboardController;
use App\Domains\SelfService\Http\Controllers\ProfileController;
use App\Domains\SelfService\Http\Controllers\ServiceAnalyticsController;
use App\Domains\SelfService\Http\Controllers\ServiceCatalogController;
use App\Domains\SelfService\Http\Controllers\ServiceDuplicateAndMergeController;
use App\Domains\SelfService\Http\Controllers\ServiceFeedbackController;
use App\Domains\SelfService\Http\Controllers\ServiceRequestCommentController;
use App\Domains\SelfService\Http\Controllers\ServiceRequestController;
use App\Domains\SelfService\Http\Controllers\ServiceRequestDocumentController;
use App\Domains\SelfService\Http\Controllers\UnifiedServicePortalController;
use Illuminate\Support\Facades\Route;

// Standard Portal Routes
Route::middleware('auth')->prefix('portal')->name('api.portal.')->group(function (): void {
    Route::get('dashboard', [PortalDashboardController::class, 'employee'])->name('dashboard');
    Route::get('manager/dashboard', [PortalDashboardController::class, 'manager'])->name('manager.dashboard');
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::match(['put', 'patch'], 'profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/change-requests', [ProfileController::class, 'requestChange'])->name('profile.change-requests.store');
    Route::put('profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::put('preferences', [ProfileController::class, 'updatePreferences'])->name('preferences.update');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::put('notifications/{notification}/archive', [NotificationController::class, 'archive'])->name('notifications.archive');
    Route::get('team', [ManagerTeamController::class, 'index'])->name('team.index');
    Route::get('team/{employee}', [ManagerTeamController::class, 'show'])->name('team.show');
});

// Enterprise HR Shared Services 2.0 & Service Delivery API (Epic 2.41)
Route::prefix('api/v1/hcm')->middleware(['api'])->group(function () {
    // Unified Service Portal & Search (Single Box)
    Route::get('/portal/unified-search', [UnifiedServicePortalController::class, 'search'])->name('api.self-service.portal.search');
    Route::get('/portal/unified-dashboard', [UnifiedServicePortalController::class, 'portalDashboard'])->name('api.self-service.portal.unified-dashboard');

    // ESS / MSS Dashboards
    Route::get('/my/dashboard', [EmployeePortalController::class, 'dashboard'])->name('api.self-service.my.dashboard');
    Route::get('/manager/dashboard', [ManagerPortalController::class, 'dashboard'])->name('api.self-service.manager.dashboard');
    Route::get('/agent/workspace', [HrAgentWorkspaceController::class, 'index'])->name('api.self-service.agent.workspace');

    // Unified Agent Shared Services Workspace
    Route::get('/shared-services/workspace', [HrSharedServicesWorkspaceController::class, 'workspace'])->name('api.shared-services.workspace');
    Route::get('/shared-services/employees/{employee}/context', [HrSharedServicesWorkspaceController::class, 'employeeContext'])->name('api.shared-services.employee.context');
    Route::get('/requests/{request}/ai-advisory', [HrSharedServicesWorkspaceController::class, 'aiAdvisory'])->name('api.shared-services.request.ai-advisory');

    // Service Catalog
    Route::get('/services', [ServiceCatalogController::class, 'index'])->name('api.self-service.services.index');
    Route::get('/services/{service}', [ServiceCatalogController::class, 'show'])->name('api.self-service.services.show');

    // Service Requests Lifecycle
    Route::get('/requests', [ServiceRequestController::class, 'index'])->name('api.self-service.requests.index');
    Route::post('/requests', [ServiceRequestController::class, 'store'])->name('api.self-service.requests.store');
    Route::get('/requests/{hrServiceRequest}', [ServiceRequestController::class, 'show'])->name('api.self-service.requests.show');
    Route::post('/requests/{hrServiceRequest}/comments', [ServiceRequestCommentController::class, 'store'])->name('api.self-service.requests.comments.store');
    Route::post('/requests/{hrServiceRequest}/documents', [ServiceRequestDocumentController::class, 'store'])->name('api.self-service.requests.documents.store');
    Route::post('/requests/{hrServiceRequest}/resolve', [ServiceRequestController::class, 'resolve'])->name('api.self-service.requests.resolve');
    Route::post('/requests/{hrServiceRequest}/close', [ServiceRequestController::class, 'close'])->name('api.self-service.requests.close');
    Route::post('/requests/{hrServiceRequest}/reopen', [ServiceRequestController::class, 'reopen'])->name('api.self-service.requests.reopen');

    // Duplicate Detection & Safe Merge
    Route::get('/requests/{request}/duplicates', [ServiceDuplicateAndMergeController::class, 'findDuplicates'])->name('api.self-service.requests.duplicates');
    Route::post('/requests/{primary}/merge', [ServiceDuplicateAndMergeController::class, 'merge'])->name('api.self-service.requests.merge');

    // CSAT Feedback
    Route::post('/requests/{request}/feedback', [ServiceFeedbackController::class, 'submit'])->name('api.self-service.requests.feedback.submit');
    Route::get('/feedback/summary', [ServiceFeedbackController::class, 'summary'])->name('api.self-service.feedback.summary');

    // Omnichannel Intake (Email, Teams, WhatsApp, Webhook)
    Route::post('/omnichannel/inbound/{channel}', [OmnichannelIntakeController::class, 'inboundMessage'])->name('api.self-service.omnichannel.inbound');

    // Knowledge Base
    Route::get('/knowledge', [KnowledgeBaseController::class, 'index'])->name('api.self-service.knowledge.index');
    Route::get('/knowledge/{article}', [KnowledgeBaseController::class, 'show'])->name('api.self-service.knowledge.show');
    Route::post('/knowledge/{article}/feedback', [KnowledgeBaseController::class, 'feedback'])->name('api.self-service.knowledge.feedback');

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('api.self-service.announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('api.self-service.announcements.store');
    Route::post('/announcements/{announcement}/acknowledge', [AnnouncementController::class, 'acknowledge'])->name('api.self-service.announcements.acknowledge');

    // Service Analytics & Reports
    Route::get('/service-reports/summary', [ServiceAnalyticsController::class, 'summary'])->name('api.self-service.reports.summary');
    Route::get('/service-reports/by-category', [ServiceAnalyticsController::class, 'byCategory'])->name('api.self-service.reports.category');
});
