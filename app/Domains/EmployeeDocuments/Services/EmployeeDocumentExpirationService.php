<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\EmployeeDocuments\Enums\DocumentStatus;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\EmployeeDocumentExpirationEvent;
use Carbon\Carbon;

class EmployeeDocumentExpirationService
{
    public function checkExpirations(string $tenantId): array
    {
        $today = Carbon::today();
        $results = [
            'expired' => 0,
            'within_7_days' => 0,
            'within_30_days' => 0,
            'within_60_days' => 0,
            'within_90_days' => 0,
            'events_created' => 0,
        ];

        $documents = EmployeeDocument::where('tenant_id', $tenantId)
            ->whereNotNull('expiry_date')
            ->whereNotIn('status', [DocumentStatus::ARCHIVED->value, DocumentStatus::REPLACED->value])
            ->get();

        foreach ($documents as $doc) {
            $expiry = Carbon::parse($doc->expiry_date)->startOfDay();
            $diffDays = $today->diffInDays($expiry, false);

            if ($diffDays < 0) {
                // Expired
                $results['expired']++;
                if ($doc->status !== DocumentStatus::EXPIRED->value) {
                    $doc->update(['status' => DocumentStatus::EXPIRED->value]);
                }
                $this->recordMilestone($doc, 'expired', $results);
            } elseif ($diffDays <= 7) {
                $results['within_7_days']++;
                $this->recordMilestone($doc, '7_days', $results);
            } elseif ($diffDays <= 30) {
                $results['within_30_days']++;
                $this->recordMilestone($doc, '30_days', $results);
            } elseif ($diffDays <= 60) {
                $results['within_60_days']++;
                $this->recordMilestone($doc, '60_days', $results);
            } elseif ($diffDays <= 90) {
                $results['within_90_days']++;
                $this->recordMilestone($doc, '90_days', $results);
            }
        }

        return $results;
    }

    protected function recordMilestone(EmployeeDocument $doc, string $milestone, array &$results): void
    {
        $existing = EmployeeDocumentExpirationEvent::where('employee_document_id', $doc->id)
            ->where('milestone', $milestone)
            ->first();

        if (!$existing) {
            EmployeeDocumentExpirationEvent::create([
                'tenant_id' => $doc->tenant_id,
                'employee_document_id' => $doc->id,
                'milestone' => $milestone,
                'recorded_at' => now(),
                'notified' => true,
            ]);
            $results['events_created']++;
        }
    }
}
