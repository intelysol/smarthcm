<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\HcmComplianceEscalation;
use App\Domains\Compliance\Models\HcmComplianceTask;
use App\Domains\Compliance\Models\HcmEmployeeLicense;
use App\Domains\Compliance\Models\HcmEmployeeVisaRecord;
use App\Domains\Compliance\Models\HcmEmployeeWorkPermit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComplianceAlertEscalationService
{
    /**
     * Run proactive expiration scan and record escalations.
     */
    public function scanAndEscalate(string $tenantId): Collection
    {
        $escalations = collect();
        $today = Carbon::today();

        // Warning tiers in days
        $tiers = [
            90 => ['tier' => '90_days', 'role' => 'employee'],
            60 => ['tier' => '60_days', 'role' => 'employee'],
            30 => ['tier' => '30_days', 'role' => 'manager'],
            14 => ['tier' => '14_days', 'role' => 'hr'],
            7  => ['tier' => '7_days',  'role' => 'compliance_officer'],
            1  => ['tier' => '1_day',   'role' => 'compliance_officer'],
        ];

        // 1. Check Work Permits
        $permits = HcmEmployeeWorkPermit::where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->where('expiry_date', '>=', $today->toDateString())
            ->get();

        foreach ($permits as $permit) {
            $daysRemaining = (int) $today->diffInDays($permit->expiry_date, false);
            foreach ($tiers as $threshold => $info) {
                if ($daysRemaining <= $threshold && $daysRemaining > ($threshold - 10)) {
                    $esc = $this->createEscalationIfNotExists($tenantId, $permit->employee_id, HcmEmployeeWorkPermit::class, $permit->id, $info['tier'], $info['role']);
                    if ($esc) {
                        $escalations->push($esc);
                    }
                    break;
                }
            }
        }

        // 2. Check Visas
        $visas = HcmEmployeeVisaRecord::where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->where('expiry_date', '>=', $today->toDateString())
            ->get();

        foreach ($visas as $visa) {
            $daysRemaining = (int) $today->diffInDays($visa->expiry_date, false);
            foreach ($tiers as $threshold => $info) {
                if ($daysRemaining <= $threshold && $daysRemaining > ($threshold - 10)) {
                    $esc = $this->createEscalationIfNotExists($tenantId, $visa->employee_id, HcmEmployeeVisaRecord::class, $visa->id, $info['tier'], $info['role']);
                    if ($esc) {
                        $escalations->push($esc);
                    }
                    break;
                }
            }
        }

        // 3. Check Licenses
        $licenses = HcmEmployeeLicense::where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', $today->toDateString())
            ->get();

        foreach ($licenses as $license) {
            $daysRemaining = (int) $today->diffInDays($license->expiry_date, false);
            foreach ($tiers as $threshold => $info) {
                if ($daysRemaining <= $threshold && $daysRemaining > ($threshold - 10)) {
                    $esc = $this->createEscalationIfNotExists($tenantId, $license->employee_id, HcmEmployeeLicense::class, $license->id, $info['tier'], $info['role']);
                    if ($esc) {
                        $escalations->push($esc);
                    }
                    break;
                }
            }
        }

        return $escalations;
    }

    /**
     * Create escalation and matching task if not already triggered today.
     */
    protected function createEscalationIfNotExists(
        string $tenantId,
        string $employeeId,
        string $targetType,
        string $targetId,
        string $tier,
        string $role
    ): ?HcmComplianceEscalation {
        $existing = HcmComplianceEscalation::where('tenant_id', $tenantId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('tier', $tier)
            ->first();

        if ($existing) {
            return null;
        }

        $escalation = HcmComplianceEscalation::create([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'tier' => $tier,
            'recipient_role' => $role,
            'triggered_at' => Carbon::now(),
            'is_acknowledged' => false,
        ]);

        // Create renewal task
        HcmComplianceTask::create([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'task_type' => 'renew_item',
            'title' => "Upcoming Expiration Warning ({$tier})",
            'description' => "Compliance document expiring soon. Action required by {$role}.",
            'due_date' => Carbon::today()->addDays(7)->toDateString(),
            'status' => 'pending',
        ]);

        return $escalation;
    }
}
