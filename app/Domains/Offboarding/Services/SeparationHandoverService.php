<?php

namespace App\Domains\Offboarding\Services;

use App\Domains\Offboarding\Enums\HandoverStatus;
use App\Domains\Offboarding\Models\SeparationHandoverItem;
use App\Domains\Offboarding\Models\SeparationHandoverRecord;
use App\Domains\Offboarding\Models\SeparationRequest;
use App\Models\User;

class SeparationHandoverService
{
    public function createHandoverRecord(SeparationRequest $request, array $data): SeparationHandoverRecord
    {
        $record = SeparationHandoverRecord::create([
            'tenant_id' => $request->tenant_id,
            'separation_request_id' => $request->id,
            'successor_employee_id' => $data['successor_employee_id'] ?? null,
            'handover_date' => $data['handover_date'] ?? null,
            'status' => HandoverStatus::PENDING->value,
            'handover_notes' => $data['handover_notes'] ?? null,
        ]);

        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                SeparationHandoverItem::create([
                    'tenant_id' => $request->tenant_id,
                    'separation_handover_record_id' => $record->id,
                    'title' => $item['title'],
                    'category' => $item['category'] ?? 'responsibility',
                    'status' => HandoverStatus::PENDING->value,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        }

        return $record;
    }

    public function verifyHandover(SeparationHandoverRecord $record, User $manager): SeparationHandoverRecord
    {
        $record->items()->update(['status' => HandoverStatus::COMPLETED->value]);

        $record->update([
            'status' => HandoverStatus::VERIFIED->value,
            'manager_verified_by' => $manager->id,
            'manager_verified_at' => now(),
        ]);

        return $record->fresh(['items']);
    }
}
