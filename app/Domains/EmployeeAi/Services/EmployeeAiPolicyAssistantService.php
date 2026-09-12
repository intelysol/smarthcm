<?php

namespace App\Domains\EmployeeAi\Services;

class EmployeeAiPolicyAssistantService
{
    public function answerPolicy(string $query, string $tenantId): array
    {
        $q = strtolower($query);

        if (str_contains($q, 'carry forward') || str_contains($q, 'carryover')) {
            return [
                'content' => "Under the Enterprise Leave Policy, eligible full-time employees may carry forward up to a maximum of 15 unused annual leave days into the subsequent calendar year. Unused days beyond 15 are forfeited unless authorized by HR under exceptional business operating conditions.",
                'citations' => [
                    [
                        'source' => 'Enterprise Leave Policy',
                        'version' => 'v4.2',
                        'section' => 'Section 6.3: Annual Leave Carryover',
                        'effective_date' => '2026-01-01',
                    ],
                ],
            ];
        }

        if (str_contains($q, 'remote') || str_contains($q, 'work from home')) {
            return [
                'content' => "The Flexible & Remote Work Guidelines permit eligible non-shift roles up to 2 remote work days per week with direct line manager coordination and operational shift coverage approval.",
                'citations' => [
                    [
                        'source' => 'Flexible Workplace Policy',
                        'version' => 'v2.1',
                        'section' => 'Section 3: Remote Work Allocation',
                        'effective_date' => '2026-03-01',
                    ],
                ],
            ];
        }

        return [
            'content' => "According to the Employee Handbook and standard operating guidelines, all employee requests should follow standard HR approval workflows. If you need further clarification, please feel free to submit an HR service inquiry.",
            'citations' => [
                [
                    'source' => 'Employee Handbook',
                    'version' => 'v2026.1',
                    'section' => 'General Employee Guidelines',
                    'effective_date' => '2026-01-01',
                ],
            ],
        ];
    }
}
