<?php

use App\Domains\EmployeeRelations\Http\Controllers\AnonymousIntakeController;
use App\Domains\EmployeeRelations\Http\Controllers\AppealController;
use App\Domains\EmployeeRelations\Http\Controllers\CaseConfigurationController;
use App\Domains\EmployeeRelations\Http\Controllers\CaseController;
use App\Domains\EmployeeRelations\Http\Controllers\DecisionController;
use App\Domains\EmployeeRelations\Http\Controllers\EmployeePortalController;
use App\Domains\EmployeeRelations\Http\Controllers\EvidenceController;
use App\Domains\EmployeeRelations\Http\Controllers\HearingController;
use App\Domains\EmployeeRelations\Http\Controllers\InvestigationController;
use App\Domains\EmployeeRelations\Http\Controllers\ReportAndAnalyticsController;
use Illuminate\Support\Facades\Route;

// 1. Anonymous Public Routes (No auth required)
Route::prefix('v1/hcm/employee-relations/anonymous')->group(function () {
    Route::post('/', [AnonymousIntakeController::class, 'submit'])->name('api.er.anonymous.submit');
    Route::get('{token}', [AnonymousIntakeController::class, 'track'])->name('api.er.anonymous.track');
    Route::post('{token}/message', [AnonymousIntakeController::class, 'postMessage'])->name('api.er.anonymous.message');
});

// 2. Authenticated Routes
Route::middleware('auth')->prefix('v1/hcm')->group(function () {

    // Employee Self-Service (ESS)
    Route::prefix('me/employee-relations')->group(function () {
        Route::post('report', [EmployeePortalController::class, 'submitReport'])->name('api.er.me.report');
        Route::get('cases', [EmployeePortalController::class, 'myCases'])->name('api.er.me.cases');
        Route::get('cases/{case}', [EmployeePortalController::class, 'myCaseDetail'])->name('api.er.me.case');
    });

    // HR Case Management Platform
    Route::prefix('employee-relations')->group(function () {

        // Global Case Routes
        Route::get('cases', [CaseController::class, 'index'])->name('api.er.cases.index');
        Route::post('cases', [CaseController::class, 'store'])->name('api.er.cases.store');
        Route::get('cases/{case}', [CaseController::class, 'show'])->name('api.er.cases.show');
        Route::patch('cases/{case}', [CaseController::class, 'update'])->name('api.er.cases.update');

        // Case Lifecycle & Actions
        Route::post('cases/{case}/assign', [CaseController::class, 'assign'])->name('api.er.cases.assign');
        Route::post('cases/{case}/triage', [CaseController::class, 'triage'])->name('api.er.cases.triage');
        Route::post('cases/{case}/close', [CaseController::class, 'close'])->name('api.er.cases.close');
        Route::post('cases/{case}/reopen', [CaseController::class, 'reopen'])->name('api.er.cases.reopen');

        // Participants & Conflicts
        Route::get('cases/{case}/participants', [CaseController::class, 'getParticipants'])->name('api.er.cases.participants.index');
        Route::post('cases/{case}/participants', [CaseController::class, 'addParticipant'])->name('api.er.cases.participants.store');
        Route::get('cases/{case}/conflicts', [CaseController::class, 'getConflicts'])->name('api.er.cases.conflicts.index');
        Route::post('cases/{case}/conflicts', [CaseController::class, 'declareConflict'])->name('api.er.cases.conflicts.store');

        // Investigations
        Route::get('cases/{case}/investigation', [InvestigationController::class, 'getInvestigation'])->name('api.er.investigations.show');
        Route::post('cases/{case}/investigation', [InvestigationController::class, 'startInvestigation'])->name('api.er.investigations.store');
        Route::post('cases/{case}/investigation/steps', [InvestigationController::class, 'addStep'])->name('api.er.investigations.steps.store');
        Route::patch('cases/{case}/investigation/steps/{step}', [InvestigationController::class, 'updateStep'])->name('api.er.investigations.steps.update');
        Route::post('cases/{case}/investigation/questions', [InvestigationController::class, 'addQuestion'])->name('api.er.investigations.questions.store');
        Route::patch('cases/{case}/investigation/questions/{question}', [InvestigationController::class, 'updateQuestion'])->name('api.er.investigations.questions.update');

        // Allegations
        Route::get('cases/{case}/allegations', [InvestigationController::class, 'getAllegations'])->name('api.er.allegations.index');
        Route::post('cases/{case}/allegations', [InvestigationController::class, 'addAllegation'])->name('api.er.allegations.store');

        // Statements
        Route::get('cases/{case}/statements', [InvestigationController::class, 'getStatements'])->name('api.er.statements.index');
        Route::post('cases/{case}/statements', [InvestigationController::class, 'recordStatement'])->name('api.er.statements.store');

        // Interviews
        Route::get('cases/{case}/interviews', [InvestigationController::class, 'getInterviews'])->name('api.er.interviews.index');
        Route::post('cases/{case}/interviews', [InvestigationController::class, 'scheduleInterview'])->name('api.er.interviews.store');
        Route::post('cases/{case}/interviews/{interview}/notes', [InvestigationController::class, 'addInterviewNotes'])->name('api.er.interviews.notes.store');

        // Evidence
        Route::get('cases/{case}/evidence', [EvidenceController::class, 'index'])->name('api.er.evidence.index');
        Route::post('cases/{case}/evidence', [EvidenceController::class, 'store'])->name('api.er.evidence.store');
        Route::get('cases/{case}/evidence/{evidence}', [EvidenceController::class, 'show'])->name('api.er.evidence.show');
        Route::get('cases/{case}/evidence/{evidence}/history', [EvidenceController::class, 'history'])->name('api.er.evidence.history');
        Route::get('cases/{case}/evidence/{evidence}/download', [EvidenceController::class, 'download'])->name('api.er.evidence.download');
        Route::post('cases/{case}/evidence/{evidence}/dispose', [EvidenceController::class, 'dispose'])->name('api.er.evidence.dispose');

        // Hearings
        Route::get('cases/{case}/hearings', [HearingController::class, 'index'])->name('api.er.hearings.index');
        Route::post('cases/{case}/hearings', [HearingController::class, 'store'])->name('api.er.hearings.store');
        Route::post('cases/{case}/hearings/{hearing}/outcome', [HearingController::class, 'recordOutcome'])->name('api.er.hearings.outcome.store');

        // Findings
        Route::get('cases/{case}/findings', [InvestigationController::class, 'getFindings'])->name('api.er.findings.index');
        Route::post('cases/{case}/findings', [InvestigationController::class, 'recordFinding'])->name('api.er.findings.store');

        // Decisions
        Route::get('cases/{case}/decision', [DecisionController::class, 'getDecision'])->name('api.er.decisions.show');
        Route::post('cases/{case}/decision', [DecisionController::class, 'recordDecision'])->name('api.er.decisions.store');

        // Corrective Actions
        Route::get('cases/{case}/actions', [DecisionController::class, 'getActions'])->name('api.er.actions.index');
        Route::post('cases/{case}/actions', [DecisionController::class, 'addAction'])->name('api.er.actions.store');
        Route::post('cases/{case}/actions/{action}/acknowledge', [DecisionController::class, 'acknowledgeAction'])->name('api.er.actions.acknowledge');
        Route::post('cases/{case}/actions/{action}/complete', [DecisionController::class, 'completeAction'])->name('api.er.actions.complete');
        Route::post('cases/{case}/actions/{action}/verify', [DecisionController::class, 'verifyAction'])->name('api.er.actions.verify');

        // Appeals
        Route::get('cases/{case}/appeals', [AppealController::class, 'index'])->name('api.er.appeals.index');
        Route::post('cases/{case}/appeals', [AppealController::class, 'store'])->name('api.er.appeals.store');
        Route::post('cases/{case}/appeals/{appeal}/resolve', [AppealController::class, 'resolve'])->name('api.er.appeals.resolve');

        // Notes & Tasks
        Route::get('cases/{case}/notes', [CaseController::class, 'getNotes'])->name('api.er.notes.index');
        Route::post('cases/{case}/notes', [CaseController::class, 'addNote'])->name('api.er.notes.store');
        Route::get('cases/{case}/tasks', [CaseController::class, 'getTasks'])->name('api.er.tasks.index');
        Route::post('cases/{case}/tasks', [CaseController::class, 'addTask'])->name('api.er.tasks.store');

        // Timeline, Audit & Export
        Route::get('cases/{case}/timeline', [CaseController::class, 'getTimeline'])->name('api.er.timeline.show');
        Route::get('cases/{case}/audit', [CaseController::class, 'getAudit'])->name('api.er.audit.show');
        Route::post('cases/{case}/export', [CaseController::class, 'export'])->name('api.er.export.store');
        Route::post('cases/{case}/ai/summary', [CaseController::class, 'generateAiSummary'])->name('api.er.ai.summary');

        // Reports & Analytics
        Route::get('reports', [ReportAndAnalyticsController::class, 'reports'])->name('api.er.reports.index');
        Route::get('analytics', [ReportAndAnalyticsController::class, 'analytics'])->name('api.er.analytics.index');

        // Case Configuration & Metadata
        Route::get('case-types', [CaseConfigurationController::class, 'getCaseTypes'])->name('api.er.case_types.index');
        Route::post('case-types', [CaseConfigurationController::class, 'storeCaseType'])->name('api.er.case_types.store');
        Route::get('policy-references', [CaseConfigurationController::class, 'getPolicyReferences'])->name('api.er.policies.index');
        Route::post('policy-references', [CaseConfigurationController::class, 'storePolicyReference'])->name('api.er.policies.store');
        Route::get('retention-policies', [CaseConfigurationController::class, 'getRetentionPolicies'])->name('api.er.retention.index');
        Route::post('retention-policies', [CaseConfigurationController::class, 'storeRetentionPolicy'])->name('api.er.retention.store');
        Route::post('retention/dispose', [CaseConfigurationController::class, 'disposeCase'])->name('api.er.retention.dispose');

        // Legal Holds
        Route::get('cases/{case}/legal-holds', [CaseConfigurationController::class, 'getLegalHolds'])->name('api.er.legal_holds.index');
        Route::post('cases/{case}/legal-holds', [CaseConfigurationController::class, 'placeLegalHold'])->name('api.er.legal_holds.store');
        Route::post('cases/{case}/legal-holds/{hold}/release', [CaseConfigurationController::class, 'releaseLegalHold'])->name('api.er.legal_holds.release');
    });
});
