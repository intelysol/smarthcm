<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Events\EmployeeSuggestionAccepted;
use App\Domains\Engagement\Events\EmployeeSuggestionCreated;
use App\Domains\Engagement\Events\EmployeeSuggestionImplemented;
use App\Domains\Engagement\Models\EmployeeSuggestion;
use App\Domains\Engagement\Models\EmployeeSuggestionVote;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EmployeeSuggestionService
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function submitSuggestion(
        string $tenantId,
        array $data,
        ?Employee $employee = null
    ): EmployeeSuggestion {
        return DB::transaction(function () use ($tenantId, $data, $employee) {
            $isAnonymous = $data['is_anonymous'] ?? false;

            $suggestion = EmployeeSuggestion::query()->create([
                'tenant_id' => $tenantId,
                'employee_id' => $isAnonymous ? null : $employee?->id,
                'is_anonymous' => $isAnonymous,
                'category' => $data['category'] ?? 'general',
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => 'submitted',
                'votes_count' => 0,
            ]);

            EmployeeSuggestionCreated::dispatch($suggestion);

            $this->audit->record(
                $tenantId,
                'EmployeeSuggestionCreated',
                'submit_suggestion',
                EmployeeSuggestion::class,
                (string) $suggestion->id,
                $employee?->user_id,
                null,
                ['title' => $suggestion->title, 'is_anonymous' => $isAnonymous]
            );

            return $suggestion;
        });
    }

    public function voteSuggestion(EmployeeSuggestion $suggestion, Employee $employee): EmployeeSuggestionVote
    {
        return DB::transaction(function () use ($suggestion, $employee) {
            $vote = EmployeeSuggestionVote::query()->firstOrCreate(
                [
                    'tenant_id' => $suggestion->tenant_id,
                    'suggestion_id' => $suggestion->id,
                    'employee_id' => $employee->id,
                ]
            );

            if ($vote->wasRecentlyCreated) {
                $suggestion->increment('votes_count');
            }

            return $vote;
        });
    }

    public function removeVote(EmployeeSuggestion $suggestion, Employee $employee): void
    {
        DB::transaction(function () use ($suggestion, $employee) {
            $deleted = EmployeeSuggestionVote::query()
                ->where('suggestion_id', $suggestion->id)
                ->where('employee_id', $employee->id)
                ->delete();

            if ($deleted > 0) {
                $suggestion->decrement('votes_count');
            }
        });
    }

    public function reviewSuggestion(
        EmployeeSuggestion $suggestion,
        string $status,
        ?string $notes = null,
        ?Employee $reviewer = null
    ): EmployeeSuggestion {
        return DB::transaction(function () use ($suggestion, $status, $notes, $reviewer) {
            $suggestion->update([
                'status' => $status,
                'review_notes' => $notes,
                'reviewed_by' => $reviewer?->id,
                'reviewed_at' => now(),
            ]);

            if ($status === 'accepted') {
                EmployeeSuggestionAccepted::dispatch($suggestion);
            } elseif ($status === 'implemented') {
                EmployeeSuggestionImplemented::dispatch($suggestion);
            }

            $this->audit->record(
                (string) $suggestion->tenant_id,
                'EmployeeSuggestionReviewed',
                'review_suggestion',
                EmployeeSuggestion::class,
                (string) $suggestion->id,
                $reviewer?->user_id,
                null,
                ['status' => $status, 'notes' => $notes]
            );

            return $suggestion->fresh();
        });
    }
}
