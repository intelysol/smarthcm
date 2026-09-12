<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;
use App\Models\User;

class EmployeeTimelineService
{
    public function getTimelineEvents(Employee $employee, User $viewer): array
    {
        $events = [];

        // 1. Joining Event
        if ($employee->joining_date) {
            $events[] = [
                'id' => 'evt-join-' . $employee->id,
                'date' => $employee->joining_date->toDateString(),
                'title' => 'Joined Organization',
                'category' => 'core_hr',
                'description' => "Joined as {$employee->designation?->name} in {$employee->department?->name}.",
                'icon' => 'fa-user-check',
            ];
        }

        // 2. Confirmation Event
        if ($employee->confirmation_date) {
            $events[] = [
                'id' => 'evt-conf-' . $employee->id,
                'date' => $employee->confirmation_date->toDateString(),
                'title' => 'Employment Confirmed',
                'category' => 'lifecycle',
                'description' => 'Successfully completed probationary evaluation.',
                'icon' => 'fa-badge-check',
            ];
        }

        // 3. Certifications Completed
        try {
            foreach ($employee->certifications as $cert) {
                if ($cert->issue_date) {
                    $events[] = [
                        'id' => 'evt-cert-' . $cert->id,
                        'date' => $cert->issue_date->toDateString(),
                        'title' => 'Professional Certification Completed',
                        'category' => 'learning',
                        'description' => $cert->name . ($cert->issuing_organization ? ' (' . $cert->issuing_organization . ')' : ''),
                        'icon' => 'fa-certificate',
                    ];
                }
            }
        } catch (\Throwable) {}

        // 4. Sort Chronologically (latest first)
        usort($events, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return $events;
    }
}
