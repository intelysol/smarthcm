<?php

namespace App\Domains\Benefits\Services;

use App\Domains\Benefits\Models\BenefitPlan;
use App\Domains\Benefits\Models\BenefitProgram;
use App\Domains\Benefits\Models\BenefitProgramVersion;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BenefitProgramService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function createProgram(string $tenantId, array $data, ?User $creator = null): BenefitProgram
    {
        return DB::transaction(function () use ($tenantId, $data, $creator) {
            $program = BenefitProgram::create([
                'tenant_id' => $tenantId,
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'health_medical',
                'status' => $data['status'] ?? 'active',
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Create initial version
            BenefitProgramVersion::create([
                'tenant_id' => $tenantId,
                'benefit_program_id' => $program->id,
                'version_number' => 1,
                'effective_from' => $data['effective_from'] ?? now()->toDateString(),
                'effective_to' => $data['effective_to'] ?? null,
                'configuration' => $data['configuration'] ?? [],
                'is_active' => true,
            ]);

            $this->auditService->record(
                tenantId: $tenantId,
                eventType: 'benefit_program.created',
                action: 'create',
                entityType: BenefitProgram::class,
                entityId: $program->id,
                actorId: $creator?->id,
                after: $program->toArray()
            );

            return $program;
        });
    }

    public function assignPlanToProgram(BenefitProgram $program, BenefitPlan $plan, ?User $actor = null): BenefitPlan
    {
        $before = $plan->toArray();
        $plan->update(['benefit_program_id' => $program->id]);

        $this->auditService->record(
            tenantId: $program->tenant_id,
            eventType: 'benefit_plan.assigned_to_program',
            action: 'update',
            entityType: BenefitPlan::class,
            entityId: $plan->id,
            actorId: $actor?->id,
            before: $before,
            after: $plan->toArray()
        );

        return $plan;
    }

    public function getProgramsWithPlans(string $tenantId): Collection
    {
        return BenefitProgram::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['plans' => function ($q) {
                $q->where('status', 'active')->with(['coverages', 'provider', 'category']);
            }, 'versions'])
            ->get();
    }
}
