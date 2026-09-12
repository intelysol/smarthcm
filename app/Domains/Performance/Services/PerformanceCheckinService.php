<?php

declare(strict_types=1);

namespace App\Domains\Performance\Services;

use App\Domains\Performance\Events\PerformanceCheckinCompleted;
use App\Domains\Performance\Models\PerformanceCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformanceCheckinService
{
    /**
     * Schedule a continuous 1-on-1 performance check-in.
     */
    public function scheduleCheckin(array $data, ?User $actor = null): PerformanceCheckin
    {
        return PerformanceCheckin::create([
            'tenant_id' => $data['tenant_id'],
            'cycle_id' => $data['cycle_id'],
            'employee_id' => $data['employee_id'],
            'manager_id' => $data['manager_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'agenda_items' => $data['agenda_items'] ?? [],
            'summary' => $data['summary'] ?? null,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Complete checkin with discussion notes, blockers, and comments.
     */
    public function completeCheckin(
        PerformanceCheckin $checkin,
        array $data,
        ?User $actor = null
    ): PerformanceCheckin {
        return DB::transaction(function () use ($checkin, $data) {
            $checkin->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
                'summary' => $data['summary'] ?? $checkin->summary,
                'employee_comment' => $data['employee_comment'] ?? $checkin->employee_comment,
                'manager_comment' => $data['manager_comment'] ?? $checkin->manager_comment,
                'agenda_items' => $data['agenda_items'] ?? $checkin->agenda_items,
            ]);

            event(new PerformanceCheckinCompleted($checkin));

            return $checkin->fresh();
        });
    }

    /**
     * List checkins for an employee.
     */
    public function getEmployeeCheckins(string $employeeId, ?string $cycleId = null): Collection
    {
        $query = PerformanceCheckin::where('employee_id', $employeeId);
        if ($cycleId) {
            $query->where('cycle_id', $cycleId);
        }
        return $query->orderByDesc('scheduled_at')->get();
    }
}
