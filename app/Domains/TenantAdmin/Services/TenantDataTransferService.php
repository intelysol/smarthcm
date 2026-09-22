<?php

namespace App\Domains\TenantAdmin\Services;

use App\Domains\TenantAdmin\Models\HcmTenantDataTransfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantDataTransferService
{
    public function validateAndProcessImport(
        string $tenantId,
        string $domain,
        array $rows,
        ?string $userId = null
    ): HcmTenantDataTransfer {
        $total = count($rows);
        $valid = 0;
        $invalid = 0;
        $warnings = 0;
        $errors = [];

        $companyId = DB::table('companies')->where('tenant_id', $tenantId)->value('id');
        if (!$companyId) {
            $companyId = (string) Str::uuid();
            DB::table('companies')->insert([
                'id' => $companyId,
                'tenant_id' => $tenantId,
                'name' => 'Default Company',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        foreach ($rows as $index => $row) {
            $rowErrors = [];

            if ($domain === 'EMPLOYEES') {
                if (empty($row['employee_code'])) {
                    $rowErrors[] = "Row {$index}: Missing employee_code";
                }
                if (empty($row['first_name'])) {
                    $rowErrors[] = "Row {$index}: Missing first_name";
                }

                if (empty($rowErrors)) {
                    $valid++;
                    // Insert into employees table if valid
                    DB::table('employees')->updateOrInsert(
                        [
                            'tenant_id' => $tenantId,
                            'employee_code' => $row['employee_code'],
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'company_id' => $companyId,
                            'employee_number' => $row['employee_code'],
                            'first_name' => $row['first_name'],
                            'last_name' => $row['last_name'] ?? '',
                            'employment_status' => 'ACTIVE',
                            'joining_date' => $row['joining_date'] ?? Carbon::now()->toDateString(),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    );
                } else {
                    $invalid++;
                    $errors = array_merge($errors, $rowErrors);
                }
            } else {
                $valid++;
            }
        }

        return HcmTenantDataTransfer::create([
            'tenant_id' => $tenantId,
            'transfer_type' => 'IMPORT',
            'domain' => $domain,
            'file_name' => "import_{$domain}_" . Carbon::now()->format('Ymd_His') . ".csv",
            'status' => $invalid > 0 && $valid === 0 ? 'FAILED' : 'COMPLETED',
            'total_rows' => $total,
            'valid_rows' => $valid,
            'invalid_rows' => $invalid,
            'warnings_count' => $warnings,
            'error_report' => $errors,
            'initiated_by_user_id' => $userId,
        ]);
    }

    public function initiateExport(
        string $tenantId,
        string $domain,
        ?string $userId = null
    ): HcmTenantDataTransfer {
        $count = match ($domain) {
            'EMPLOYEES' => DB::table('employees')->where('tenant_id', $tenantId)->count(),
            'DEPARTMENTS' => DB::table('departments')->where('tenant_id', $tenantId)->count(),
            default => 0,
        };

        return HcmTenantDataTransfer::create([
            'tenant_id' => $tenantId,
            'transfer_type' => 'EXPORT',
            'domain' => $domain,
            'file_name' => "export_{$domain}_" . Carbon::now()->format('Ymd_His') . ".csv",
            'status' => 'COMPLETED',
            'total_rows' => $count,
            'valid_rows' => $count,
            'invalid_rows' => 0,
            'warnings_count' => 0,
            'error_report' => [],
            'initiated_by_user_id' => $userId,
        ]);
    }
}
