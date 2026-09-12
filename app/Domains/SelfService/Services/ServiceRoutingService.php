<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceTeam;

class ServiceRoutingService
{
    /**
     * Intelligently determine the target Queue or Specialized Team for a request/case.
     */
    public function determineRoute(HrServiceDefinition $service, Employee $employee, array $attributes = []): array
    {
        $tenantId = $employee->tenant_id;
        $category = $service->category?->name ?? 'General HR';
        $priority = $attributes['priority'] ?? 'normal';
        $confidentiality = $service->confidentiality_level ?? 'normal';

        // 1. Sensitive or Employee Relations requests
        if ($confidentiality === 'highly_confidential' || $confidentiality === 'restricted' || str_contains(strtolower($category), 'relations')) {
            $erQueue = HrServiceQueue::where('tenant_id', $tenantId)
                ->where('code', 'ER_SPECIALIST_QUEUE')
                ->first();

            return [
                'target_type' => 'queue',
                'target_id' => $erQueue?->id ?? $service->default_queue_id,
                'target_name' => 'Employee Relations & Sensitive Matters',
                'routing_reason' => 'Routed due to high confidentiality or employee relations category.',
            ];
        }

        // 2. Payroll and Compensation questions
        if (str_contains(strtolower($category), 'payroll') || str_contains(strtolower($category), 'compensation')) {
            $payrollQueue = HrServiceQueue::where('tenant_id', $tenantId)
                ->where('code', 'PAYROLL_SUPPORT_QUEUE')
                ->first();

            return [
                'target_type' => 'queue',
                'target_id' => $payrollQueue?->id ?? $service->default_queue_id,
                'target_name' => 'Payroll & Compensation Support Team',
                'routing_reason' => 'Routed to specialized Payroll Operations team.',
            ];
        }

        // 3. Benefits and Insurance questions
        if (str_contains(strtolower($category), 'benefit')) {
            $benefitsQueue = HrServiceQueue::where('tenant_id', $tenantId)
                ->where('code', 'BENEFITS_SUPPORT_QUEUE')
                ->first();

            return [
                'target_type' => 'queue',
                'target_id' => $benefitsQueue?->id ?? $service->default_queue_id,
                'target_name' => 'Benefits & Wellness Support Team',
                'routing_reason' => 'Routed to specialized Benefits Administration team.',
            ];
        }

        // 4. Default to Service Default Queue or HR Shared Services Queue
        $sharedServicesQueue = HrServiceQueue::where('tenant_id', $tenantId)
            ->where('code', 'HR_SHARED_SERVICES_QUEUE')
            ->first();

        return [
            'target_type' => 'queue',
            'target_id' => $service->default_queue_id ?? $sharedServicesQueue?->id,
            'target_name' => 'HR Shared Services Tier 1',
            'routing_reason' => 'Routed to standard HR Shared Services Tier 1 intake queue.',
        ];
    }
}
