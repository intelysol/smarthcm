<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityRelocationCase;
use App\Domains\Mobility\Models\MobilityRelocationItem;
use App\Models\User;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Str;

class MobilityRelocationService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Create relocation case for an assignment.
     */
    public function initiateRelocationCase(MobilityAssignment $assignment, array $data, ?User $actor = null): MobilityRelocationCase
    {
        $caseNumber = 'REL-' . strtoupper(Str::random(8));

        $case = MobilityRelocationCase::create([
            'tenant_id' => $assignment->tenant_id,
            'assignment_id' => $assignment->id,
            'case_number' => $caseNumber,
            'status' => 'initiated',
            'relocation_provider_name' => $data['relocation_provider_name'] ?? null,
            'provider_reference' => $data['provider_reference'] ?? null,
            'target_move_date' => $data['target_move_date'] ?? $assignment->start_date,
            'family_relocating' => (bool) ($data['family_relocating'] ?? false),
            'relocating_dependent_ids' => $data['relocating_dependent_ids'] ?? [],
            'notes' => $data['notes'] ?? null,
        ]);

        // Automatically populate standard relocation items
        $this->seedStandardRelocationItems($case);

        $this->auditService->record(
            tenantId: $assignment->tenant_id,
            eventType: 'relocation_case_initiated',
            action: 'create',
            entityType: 'MobilityRelocationCase',
            entityId: $case->id,
            actorId: $actor?->id ? (int) $actor->id : null,
            before: null,
            after: $case->toArray()
        );

        return $case;
    }

    /**
     * Seed standard relocation checklist items.
     */
    protected function seedStandardRelocationItems(MobilityRelocationCase $case): void
    {
        $standardItems = [
            ['item_type' => 'temporary_accommodation', 'title' => 'Temporary Living Arrangements (30 Days)'],
            ['item_type' => 'shipment', 'title' => 'Household Goods Shipment & Customs Clearance'],
            ['item_type' => 'orientation', 'title' => 'Host Destination Area Tour & Orientation'],
            ['item_type' => 'permanent_accommodation', 'title' => 'Home Finding & Lease Signing'],
            ['item_type' => 'bank_setup', 'title' => 'Local Host Bank Account Setup'],
            ['item_type' => 'local_registration', 'title' => 'Host City Registration / Social Security'],
        ];

        if ($case->family_relocating) {
            $standardItems[] = ['item_type' => 'schooling', 'title' => 'Dependent School Search & Admissions'];
        }

        foreach ($standardItems as $item) {
            MobilityRelocationItem::create([
                'tenant_id' => $case->tenant_id,
                'relocation_case_id' => $case->id,
                'item_type' => $item['item_type'],
                'title' => $item['title'],
                'status' => 'pending',
                'cost_estimate' => 0.0000,
                'currency' => 'USD',
            ]);
        }
    }

    /**
     * Complete a relocation item.
     */
    public function completeItem(MobilityRelocationItem $item): MobilityRelocationItem
    {
        $item->update([
            'status' => 'completed',
            'completed_date' => now()->toDateString(),
        ]);

        // Check if all items in case are completed
        $remaining = MobilityRelocationItem::where('relocation_case_id', $item->relocation_case_id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'waived')
            ->count();

        if ($remaining === 0) {
            $item->relocationCase->update([
                'status' => 'completed',
                'actual_move_date' => now()->toDateString(),
            ]);
        }

        return $item;
    }
}
