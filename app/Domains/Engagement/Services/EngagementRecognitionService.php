<?php

namespace App\Domains\Engagement\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Events\EmployeeRecognitionCreated;
use App\Domains\Engagement\Events\EmployeeRecognitionPublished;
use App\Domains\Engagement\Models\EngagementRecognition;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class EngagementRecognitionService
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function createRecognition(
        string $tenantId,
        Employee $sender,
        Employee $recipient,
        array $data,
        bool $autoPublish = true
    ): EngagementRecognition {
        return DB::transaction(function () use ($tenantId, $sender, $recipient, $data, $autoPublish) {
            $status = $autoPublish ? 'published' : 'moderation';

            $recognition = EngagementRecognition::query()->create([
                'tenant_id' => $tenantId,
                'sender_employee_id' => $sender->id,
                'recipient_employee_id' => $recipient->id,
                'recognition_type' => $data['recognition_type'] ?? 'peer',
                'value_tag' => $data['value_tag'] ?? null,
                'title' => $data['title'],
                'message' => $data['message'],
                'visibility' => $data['visibility'] ?? 'team',
                'status' => $status,
                'likes_count' => 0,
            ]);

            EmployeeRecognitionCreated::dispatch($recognition);

            if ($status === 'published') {
                EmployeeRecognitionPublished::dispatch($recognition);
            }

            $this->audit->record(
                $tenantId,
                'EmployeeRecognitionCreated',
                'create_recognition',
                EngagementRecognition::class,
                (string) $recognition->id,
                $sender->user_id,
                null,
                ['recipient_id' => $recipient->id, 'value_tag' => $recognition->value_tag]
            );

            return $recognition;
        });
    }

    public function moderateRecognition(
        EngagementRecognition $recognition,
        string $status,
        ?Employee $moderator = null
    ): EngagementRecognition {
        return DB::transaction(function () use ($recognition, $status, $moderator) {
            $recognition->update([
                'status' => $status,
                'moderated_by' => $moderator?->id,
                'moderated_at' => now(),
            ]);

            if ($status === 'published') {
                EmployeeRecognitionPublished::dispatch($recognition);
            }

            $this->audit->record(
                (string) $recognition->tenant_id,
                'EmployeeRecognitionModerated',
                'moderate_recognition',
                EngagementRecognition::class,
                (string) $recognition->id,
                $moderator?->user_id,
                null,
                ['status' => $status]
            );

            return $recognition->fresh();
        });
    }

    public function likeRecognition(EngagementRecognition $recognition): int
    {
        $recognition->increment('likes_count');
        return $recognition->fresh()->likes_count;
    }
}
