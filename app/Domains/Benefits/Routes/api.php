<?php

use App\Domains\Benefits\Http\Controllers\BenefitCoverageController;
use App\Domains\Benefits\Http\Controllers\BenefitElectionController;
use App\Domains\Benefits\Http\Controllers\BenefitEnrollmentController;
use App\Domains\Benefits\Http\Controllers\BenefitLifeEventController;
use App\Domains\Benefits\Http\Controllers\BenefitOpenEnrollmentController;
use App\Domains\Benefits\Http\Controllers\BenefitPayrollReconciliationController;
use App\Domains\Benefits\Http\Controllers\BenefitPlanController;
use App\Domains\Benefits\Http\Controllers\BenefitProgramController;
use App\Domains\Benefits\Http\Controllers\BenefitProviderIntegrationController;
use App\Domains\Benefits\Http\Controllers\BenefitsAdministrationController;
use App\Domains\Benefits\Http\Controllers\BenefitsAiController;
use App\Domains\Benefits\Http\Controllers\BenefitsReportController;
use App\Domains\Benefits\Http\Controllers\BenefitStatementController;
use App\Domains\Benefits\Http\Controllers\BenefitWaiverController;
use App\Domains\Benefits\Http\Controllers\FinancialWellnessController;
use App\Domains\Benefits\Http\Controllers\InsuranceClaimController;
use App\Domains\Benefits\Http\Controllers\LoanApplicationController;
use App\Domains\Benefits\Http\Controllers\LoanProductController;
use App\Domains\Benefits\Http\Controllers\RetirementAccountController;
use App\Domains\Benefits\Http\Controllers\RetirementPlanController;
use App\Domains\Benefits\Http\Controllers\SalaryAdvanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/hcm/benefits')->middleware(['api', 'auth:sanctum'])->group(function () {
    // 1. Benefit Programs & Versioning
    Route::get('/programs', [BenefitProgramController::class, 'index']);
    Route::post('/programs', [BenefitProgramController::class, 'store']);
    Route::get('/programs/{program}', [BenefitProgramController::class, 'show']);
    Route::post('/programs/{program}/plans/{plan}', [BenefitProgramController::class, 'assignPlan']);

    // 2. Benefit Plans, Catalog & Coverage Tiers
    Route::get('/plans', [BenefitPlanController::class, 'index']);
    Route::post('/plans', [BenefitPlanController::class, 'store']);
    Route::get('/plans/{plan}', [BenefitPlanController::class, 'show']);
    Route::get('/plans/{plan}/coverages', [BenefitCoverageController::class, 'index']);
    Route::post('/plans/{plan}/coverages', [BenefitCoverageController::class, 'store']);
    Route::get('/plans/{plan}/coverages/estimate', [BenefitCoverageController::class, 'estimate']);

    // 3. Open Enrollment Campaigns
    Route::get('/open-enrollment', [BenefitOpenEnrollmentController::class, 'index']);
    Route::post('/open-enrollment', [BenefitOpenEnrollmentController::class, 'store']);
    Route::post('/open-enrollment/{window}/open', [BenefitOpenEnrollmentController::class, 'open']);
    Route::get('/open-enrollment/{window}/progress', [BenefitOpenEnrollmentController::class, 'progress']);
    Route::post('/open-enrollment/{window}/finalize', [BenefitOpenEnrollmentController::class, 'finalize']);

    // 4. Employee Benefit Elections (Self-Service & Admin)
    Route::get('/employees/{employee}/eligible-plans', [BenefitElectionController::class, 'eligiblePlans']);
    Route::get('/employees/{employee}/elections', [BenefitElectionController::class, 'index']);
    Route::post('/employees/{employee}/elections', [BenefitElectionController::class, 'store']);
    Route::post('/elections/{election}/confirm', [BenefitElectionController::class, 'confirm']);
    Route::post('/elections/{election}/approve', [BenefitElectionController::class, 'approve']);

    // 5. Benefit Waivers & Mandatory Benefit Rules
    Route::get('/employees/{employee}/waivers', [BenefitWaiverController::class, 'index']);
    Route::post('/employees/{employee}/waivers', [BenefitWaiverController::class, 'store']);
    Route::post('/waivers/{waiver}/approve', [BenefitWaiverController::class, 'approve']);
    Route::post('/waivers/{waiver}/reject', [BenefitWaiverController::class, 'reject']);

    // 6. Qualifying Life Events & Document Verification
    Route::get('/life-events/types', [BenefitLifeEventController::class, 'types']);
    Route::post('/life-events/types', [BenefitLifeEventController::class, 'storeType']);
    Route::get('/employees/{employee}/life-events', [BenefitLifeEventController::class, 'employeeEvents']);
    Route::post('/employees/{employee}/life-events', [BenefitLifeEventController::class, 'report']);
    Route::post('/life-events/{event}/verify', [BenefitLifeEventController::class, 'verify']);

    // 7. Benefit Providers & Integration Orchestration
    Route::get('/providers/mappings', [BenefitProviderIntegrationController::class, 'mappings']);
    Route::post('/providers/{provider}/mappings', [BenefitProviderIntegrationController::class, 'storeMapping']);
    Route::post('/providers/{provider}/export', [BenefitProviderIntegrationController::class, 'export']);

    // 8. Payroll Deductions & Reconciliation
    Route::get('/reconciliations', [BenefitPayrollReconciliationController::class, 'index']);
    Route::post('/reconciliations/periods/{period}', [BenefitPayrollReconciliationController::class, 'reconcile']);

    // 9. Employee Benefit Statements & Total Rewards
    Route::get('/employees/{employee}/statement', [BenefitStatementController::class, 'show']);
    Route::post('/employees/{employee}/statement', [BenefitStatementController::class, 'generate']);

    // 10. AI Benefits Advisory (Factual, Strict Safety Boundaries)
    Route::get('/employees/{employee}/ai/explain-plans', [BenefitsAiController::class, 'explainPlans']);
    Route::post('/employees/{employee}/ai/compare-plans', [BenefitsAiController::class, 'comparePlans']);
    Route::get('/employees/{employee}/plans/{plan}/ai/explain-eligibility', [BenefitsAiController::class, 'explainEligibility']);
    Route::post('/employees/{employee}/ai/chat', [BenefitsAiController::class, 'chat']);

    // 11. HR Administration Command Center & Bulk Operations
    Route::get('/admin/dashboard', [BenefitsAdministrationController::class, 'dashboard']);
    Route::post('/admin/bulk-operation', [BenefitsAdministrationController::class, 'bulkOperation']);

    // 12. Authoritative Active Enrollments (Legacy + Ongoing)
    Route::get('/enrollments', [BenefitEnrollmentController::class, 'index']);
    Route::post('/enrollments', [BenefitEnrollmentController::class, 'store']);
    Route::post('/enrollments/{enrollment}/approve', [BenefitEnrollmentController::class, 'approve']);

    // 13. Insurance Claims
    Route::get('/claims', [InsuranceClaimController::class, 'index']);
    Route::post('/claims', [InsuranceClaimController::class, 'store']);
    Route::get('/claims/{claim}', [InsuranceClaimController::class, 'show']);
    Route::post('/claims/{claim}/approve', [InsuranceClaimController::class, 'approve']);

    // 14. Retirement & Pension
    Route::get('/retirement/plans', [RetirementPlanController::class, 'index']);
    Route::post('/retirement/plans', [RetirementPlanController::class, 'store']);
    Route::get('/retirement/accounts', [RetirementAccountController::class, 'index']);
    Route::get('/retirement/accounts/{account}', [RetirementAccountController::class, 'show']);
    Route::post('/retirement/withdraw', [RetirementAccountController::class, 'withdraw']);

    // 15. Employee Loans
    Route::get('/loans/products', [LoanProductController::class, 'index']);
    Route::post('/loans/products', [LoanProductController::class, 'store']);
    Route::get('/loans/applications', [LoanApplicationController::class, 'index']);
    Route::post('/loans/applications', [LoanApplicationController::class, 'store']);
    Route::get('/loans/applications/{loan}', [LoanApplicationController::class, 'show']);
    Route::post('/loans/applications/{loan}/approve', [LoanApplicationController::class, 'approve']);
    Route::post('/loans/applications/{loan}/disburse', [LoanApplicationController::class, 'disburse']);
    Route::post('/loans/applications/{loan}/restructure', [LoanApplicationController::class, 'restructure']);
    Route::post('/loans/applications/{loan}/settle', [LoanApplicationController::class, 'settle']);

    // 16. Salary Advances
    Route::get('/advances', [SalaryAdvanceController::class, 'index']);
    Route::post('/advances', [SalaryAdvanceController::class, 'store']);
    Route::post('/advances/{advance}/approve', [SalaryAdvanceController::class, 'approve']);

    // 17. Financial Wellness
    Route::get('/wellness/programs', [FinancialWellnessController::class, 'index']);
    Route::post('/wellness/programs', [FinancialWellnessController::class, 'store']);

    // 18. Executive Reports
    Route::get('/reports/summary', [BenefitsReportController::class, 'executiveSummary']);
    Route::get('/reports/employee-cost/{employee}', [BenefitsReportController::class, 'employeeTotalCost']);
});
