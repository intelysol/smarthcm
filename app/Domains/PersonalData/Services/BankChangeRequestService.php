<?php

namespace App\Domains\PersonalData\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\PersonalData\Models\HcmEmployeeBankChangeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BankChangeRequestService
{
    public function __construct(
        protected PersonalDataSecurityService $securityService
    ) {}

    /**
     * Submit a new bank detail change request.
     */
    public function submitRequest(string $employeeId, array $data, ?User $actor = null): HcmEmployeeBankChangeRequest
    {
        return DB::transaction(function () use ($employeeId, $data) {
            $employee = Employee::findOrFail($employeeId);
            $accountNumber = $data['account_number'] ?? ($data['account_number_encrypted'] ?? '');
            $maskedAccount = $this->securityService->maskBankAccount($accountNumber);

            $iban = $data['iban'] ?? ($data['iban_encrypted'] ?? null);
            $maskedIban = $iban ? $this->securityService->maskBankAccount($iban) : null;

            $requestNumber = 'BCR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            return HcmEmployeeBankChangeRequest::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employeeId,
                'request_number' => $requestNumber,
                'request_type' => $data['request_type'] ?? 'update_account',
                'bank_name' => $data['bank_name'],
                'branch_name' => $data['branch_name'] ?? null,
                'account_title' => $data['account_title'],
                'account_number_encrypted' => $accountNumber, // Casted to encrypted
                'masked_account_number' => $maskedAccount,
                'iban_encrypted' => $iban,
                'masked_iban' => $maskedIban,
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'reason' => $data['reason'] ?? null,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Review a bank change request (approve or reject).
     */
    public function reviewRequest(string $requestId, string $action, ?string $reason = null, ?User $reviewer = null): HcmEmployeeBankChangeRequest
    {
        return DB::transaction(function () use ($requestId, $action, $reason, $reviewer) {
            $request = HcmEmployeeBankChangeRequest::findOrFail($requestId);

            if (!in_array($request->status, ['pending', 'under_review'])) {
                throw new InvalidArgumentException("Request cannot be {$action}ed because it is already {$request->status}.");
            }

            if ($action === 'approve') {
                $request->update([
                    'status' => 'approved',
                    'reviewed_by' => $reviewer?->id,
                    'reviewed_at' => Carbon::now(),
                ]);

                // Automatically apply to Payroll
                $this->applyToPayrollRecord($request);
            } elseif ($action === 'reject') {
                $request->update([
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'reviewed_by' => $reviewer?->id,
                    'reviewed_at' => Carbon::now(),
                ]);
            } else {
                throw new InvalidArgumentException("Invalid action: {$action}");
            }

            return $request->fresh();
        });
    }

    /**
     * Apply approved request to the underlying payroll bank account tables if they exist.
     */
    protected function applyToPayrollRecord(HcmEmployeeBankChangeRequest $request): void
    {
        // Check if legacy or payroll employee_bank_accounts table exists
        if (DB::getSchemaBuilder()->hasTable('employee_bank_accounts')) {
            $existing = DB::table('employee_bank_accounts')
                ->where('tenant_id', $request->tenant_id)
                ->where('employee_id', $request->employee_id)
                ->where('is_primary', true)
                ->first();

            if ($existing) {
                DB::table('employee_bank_accounts')
                    ->where('id', $existing->id)
                    ->update([
                        'bank' => $request->bank_name,
                        'branch' => $request->branch_name,
                        'account_title' => $request->account_title,
                        'account_number' => $request->account_number_encrypted,
                        'iban' => $request->iban_encrypted,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('employee_bank_accounts')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $request->tenant_id,
                    'employee_id' => $request->employee_id,
                    'is_primary' => true,
                    'bank' => $request->bank_name,
                    'branch' => $request->branch_name,
                    'account_title' => $request->account_title,
                    'account_number' => $request->account_number_encrypted,
                    'iban' => $request->iban_encrypted,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $request->update([
            'status' => 'applied_to_payroll',
            'payroll_actioned_at' => Carbon::now(),
            'reviewed_at' => Carbon::now(),
        ]);
    }

    /**
     * List bank change requests for an employee.
     */
    public function getRequestsForEmployee(string $employeeId): Collection
    {
        return HcmEmployeeBankChangeRequest::where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * List pending requests for Payroll administrators.
     */
    public function getPendingRequestsForPayroll(string $tenantId): Collection
    {
        return HcmEmployeeBankChangeRequest::with('employee')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'under_review'])
            ->orderByDesc('created_at')
            ->get();
    }
}
