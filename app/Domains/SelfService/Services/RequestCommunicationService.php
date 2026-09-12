<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Enums\ServiceCommentType;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Events\ServiceRequestCommentAdded;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class RequestCommunicationService
{
    public function __construct(
        protected RequestSlaService $slaService
    ) {}

    public function addComment(HrServiceRequest $request, User $user, string $message, string $type = 'public', ?array $attachments = null): HrServiceRequestComment
    {
        $employee = Employee::where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();
        $isEmployee = ($employee && $employee->id === $request->employee_id);

        // Security check: Employee cannot post internal notes
        if ($isEmployee && $type === ServiceCommentType::INTERNAL->value) {
            throw ValidationException::withMessages([
                'comment_type' => 'Employees cannot post confidential internal notes.',
            ]);
        }

        $comment = $request->comments()->create([
            'tenant_id' => $request->tenant_id,
            'user_id' => $user->id,
            'employee_id' => $employee?->id,
            'comment_type' => $type,
            'message' => $message,
            'attachments' => $attachments,
        ]);

        // If comment is by an HR agent, record first response on SLA
        if (! $isEmployee) {
            $this->slaService->recordFirstResponse($request);
        } else {
            // If employee responds and SLA was paused (e.g. waiting for employee), resume SLA
            if ($request->status === ServiceRequestStatus::WAITING_FOR_EMPLOYEE->value) {
                $request->update(['status' => ServiceRequestStatus::IN_PROGRESS->value]);
                $this->slaService->resumeSla($request);
            }
        }

        event(new ServiceRequestCommentAdded($comment));

        return $comment;
    }

    public function getVisibleCommentsForUser(HrServiceRequest $request, User $user): Collection
    {
        $employee = Employee::where('tenant_id', $user->tenant_id)->where('user_id', $user->id)->first();
        $isEmployee = ($employee && $employee->id === $request->employee_id);

        $query = $request->comments()->with(['user', 'employee'])->orderBy('created_at', 'asc');

        if ($isEmployee) {
            // Strictly filter out internal notes for employee
            $query->where('comment_type', ServiceCommentType::PUBLIC->value);
        }

        return $query->get();
    }
}
