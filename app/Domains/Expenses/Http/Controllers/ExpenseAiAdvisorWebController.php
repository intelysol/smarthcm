<?php

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Models\ExpenseCategory;
use App\Domains\Expenses\Models\ExpenseClaim;
use App\Domains\Expenses\Services\ExpenseAiAdvisoryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseAiAdvisorWebController extends Controller
{
    public function __construct(
        protected ExpenseAiAdvisoryService $aiService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';

        $categories = ExpenseCategory::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $claims = ExpenseClaim::where('tenant_id', $tenantId)->orderByDesc('created_at')->limit(10)->get();
        $employees = Employee::where('tenant_id', $tenantId)->orderBy('first_name')->limit(20)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'claims' => $claims,
                'employees' => $employees,
            ]);
        }

        return view('expenses.ai.advisor', compact('categories', 'claims', 'employees'));
    }

    public function query(Request $request): JsonResponse
    {
        $tenantId = $request->user()?->tenant_id ?? 'default';
        $type = $request->input('type', 'explain_policy');

        switch ($type) {
            case 'explain_policy':
                $categoryCode = $request->input('category_code');
                $employeeId = $request->input('employee_id');
                $employee = $employeeId ? Employee::find($employeeId) : null;
                $result = $this->aiService->explainPolicyRules($tenantId, $categoryCode, $employee);
                break;

            case 'pre_validate_claim':
                $claimId = $request->input('claim_id');
                $claim = ExpenseClaim::findOrFail($claimId);
                $result = $this->aiService->preValidateClaim($claim);
                break;

            case 'estimate_per_diem':
                $destinationType = $request->input('destination_type', 'domestic');
                $country = $request->input('country');
                $city = $request->input('city');
                $days = (int) $request->input('days', 3);
                $isDeparture = (bool) $request->input('is_departure_day', true);
                $isReturn = (bool) $request->input('is_return_day', true);
                $providedMeals = $request->input('provided_meals', []);
                $result = $this->aiService->estimatePerDiem(
                    $tenantId,
                    $destinationType,
                    $country,
                    $city,
                    $days,
                    $isDeparture,
                    $isReturn,
                    $providedMeals
                );
                break;

            case 'recommend_advance_settlement':
                $employeeId = $request->input('employee_id');
                $employee = Employee::findOrFail($employeeId);
                $claimId = $request->input('claim_id');
                $claim = $claimId ? ExpenseClaim::find($claimId) : null;
                $result = $this->aiService->recommendAdvanceSettlement($employee, $claim);
                break;

            default:
                $result = $this->aiService->executeAutonomousAction($type);
                break;
        }

        return response()->json($result);
    }
}
