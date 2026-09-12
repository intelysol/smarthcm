<?php

namespace App\Domains\SelfService\Services;

use App\Domains\SelfService\Enums\ServiceLinkType;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestComment;
use App\Domains\SelfService\Models\HrServiceRequestLink;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServiceDuplicateDetectionService
{
    /**
     * Find potential duplicate requests for a given request or parameters.
     */
    public function findPotentialDuplicates(HrServiceRequest $request, int $windowDays = 14): Collection
    {
        return HrServiceRequest::where('tenant_id', $request->tenant_id)
            ->where('id', '!=', $request->id)
            ->where('employee_id', $request->employee_id)
            ->where('created_at', '>=', Carbon::parse($request->created_at)->subDays($windowDays))
            ->where(function ($q) use ($request) {
                $q->where('hr_service_definition_id', $request->hr_service_definition_id)
                    ->orWhere('subject', 'LIKE', '%' . substr($request->subject, 0, 20) . '%');
            })
            ->whereNotIn('status', [ServiceRequestStatus::CANCELLED->value])
            ->latest()
            ->get();
    }

    /**
     * Safely merge a secondary request into a primary request.
     * Preserves comments, documents, audit trails, and closes the duplicate request.
     */
    public function mergeRequests(HrServiceRequest $primary, HrServiceRequest $secondary, User $actor, string $reason = 'Duplicate request merge'): array
    {
        return DB::transaction(function () use ($primary, $secondary, $actor, $reason) {
            // 1. Link requests bidirectionally
            HrServiceRequestLink::create([
                'tenant_id' => $primary->tenant_id,
                'parent_request_id' => $primary->id,
                'child_request_id' => $secondary->id,
                'link_type' => 'merged_into',
                'notes' => "Merged by {$actor->name}: {$reason}",
            ]);

            // 2. Add merge comment to primary request
            HrServiceRequestComment::create([
                'tenant_id' => $primary->tenant_id,
                'hr_service_request_id' => $primary->id,
                'user_id' => $actor->id,
                'comment_type' => 'internal',
                'message' => "Request #{$secondary->request_number} was merged into this request. Reason: {$reason}",
            ]);

            // 3. Add closure comment to secondary request
            HrServiceRequestComment::create([
                'tenant_id' => $secondary->tenant_id,
                'hr_service_request_id' => $secondary->id,
                'user_id' => $actor->id,
                'comment_type' => 'public',
                'message' => "This request has been merged into #{$primary->request_number}. All future updates will proceed under #{$primary->request_number}.",
            ]);

            // 4. Close secondary request as cancelled / closed
            $secondary->update([
                'status' => ServiceRequestStatus::CLOSED->value,
                'closed_at' => now(),
            ]);

            return [
                'primary_request_id' => $primary->id,
                'merged_request_id' => $secondary->id,
                'status' => 'successfully_merged',
                'timestamp' => now()->toIso8601String(),
            ];
        });
    }
}
