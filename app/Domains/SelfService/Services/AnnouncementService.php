<?php

namespace App\Domains\SelfService\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\SelfService\Events\AnnouncementAcknowledged;
use App\Domains\SelfService\Events\AnnouncementPublished;
use App\Domains\SelfService\Models\HrAnnouncement;
use App\Domains\SelfService\Models\HrAnnouncementAcknowledgement;
use App\Domains\SelfService\Models\HrAnnouncementAudience;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    public function createAnnouncement(array $data, User $author, array $audiences = []): HrAnnouncement
    {
        return DB::transaction(function () use ($data, $author, $audiences) {
            $announcement = HrAnnouncement::create([
                'tenant_id' => $author->tenant_id,
                'title' => $data['title'],
                'category' => $data['category'] ?? 'general',
                'content' => $data['content'],
                'priority' => $data['priority'] ?? 'normal',
                'requires_acknowledgement' => $data['requires_acknowledgement'] ?? false,
                'published_at' => $data['published_at'] ?? now(),
                'expires_at' => $data['expires_at'] ?? null,
                'status' => 'published',
                'author_user_id' => $author->id,
            ]);

            if (empty($audiences)) {
                // Default global tenant audience
                $announcement->audiences()->create([
                    'tenant_id' => $announcement->tenant_id,
                    'audience_type' => 'tenant',
                    'audience_id' => null,
                ]);
            } else {
                foreach ($audiences as $aud) {
                    $announcement->audiences()->create([
                        'tenant_id' => $announcement->tenant_id,
                        'audience_type' => $aud['audience_type'],
                        'audience_id' => $aud['audience_id'] ?? null,
                    ]);
                }
            }

            event(new AnnouncementPublished($announcement));

            return $announcement->fresh(['audiences']);
        });
    }

    public function getVisibleAnnouncementsForEmployee(Employee $employee): Collection
    {
        return HrAnnouncement::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->whereHas('audiences', function ($q) use ($employee) {
                $q->where('audience_type', 'tenant')
                  ->orWhere(fn ($sq) => $sq->where('audience_type', 'company')->where('audience_id', $employee->company_id))
                  ->orWhere(fn ($sq) => $sq->where('audience_type', 'branch')->where('audience_id', $employee->branch_id))
                  ->orWhere(fn ($sq) => $sq->where('audience_type', 'department')->where('audience_id', $employee->department_id));
            })
            ->with(['acknowledgements' => fn ($q) => $q->where('employee_id', $employee->id)])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function acknowledgeAnnouncement(HrAnnouncement $announcement, Employee $employee, ?string $ip = null): HrAnnouncementAcknowledgement
    {
        $ack = HrAnnouncementAcknowledgement::updateOrCreate(
            [
                'tenant_id' => $announcement->tenant_id,
                'hr_announcement_id' => $announcement->id,
                'employee_id' => $employee->id,
            ],
            [
                'viewed_at' => now(),
                'acknowledged_at' => now(),
                'ip_address' => $ip ?? '127.0.0.1',
            ]
        );

        event(new AnnouncementAcknowledged($ack));

        return $ack;
    }
}
