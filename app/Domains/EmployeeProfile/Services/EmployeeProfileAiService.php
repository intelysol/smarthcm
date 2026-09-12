<?php

namespace App\Domains\EmployeeProfile\Services;

use App\Domains\Employee\Models\Employee;

class EmployeeProfileAiService
{
    public function parseNaturalLanguageSearch(string $query): array
    {
        $criteria = [
            'is_advisory' => true,
            'job_title' => null,
            'location' => null,
            'department' => null,
            'skill' => null,
            'raw_query' => $query,
        ];

        $lower = strtolower($query);

        // Extract location hints
        $commonLocations = ['karachi', 'lahore', 'islamabad', 'dubai', 'london', 'new york', 'remote'];
        foreach ($commonLocations as $loc) {
            if (str_contains($lower, $loc)) {
                $criteria['location'] = ucfirst($loc);
                break;
            }
        }

        // Extract role/job hints
        $commonRoles = [
            'software engineer' => 'Software Engineer',
            'senior developer' => 'Senior Developer',
            'developer' => 'Developer',
            'designer' => 'Product Designer',
            'manager' => 'Manager',
            'accountant' => 'Accountant',
            'hr officer' => 'HR Officer',
        ];
        foreach ($commonRoles as $key => $role) {
            if (str_contains($lower, $key)) {
                $criteria['job_title'] = $role;
                break;
            }
        }

        // Extract skills
        $commonSkills = ['python', 'php', 'laravel', 'react', 'c#', 'sql', 'aws', 'docker', 'typescript'];
        foreach ($commonSkills as $sk) {
            if (str_contains($lower, $sk)) {
                $criteria['skill'] = strtoupper($sk);
                break;
            }
        }

        return $criteria;
    }

    public function generateProfileSummary(Employee $employee): array
    {
        $tenureYears = $employee->joining_date ? $employee->joining_date->diffInYears(now()) : 0;
        $tenureMonths = $employee->joining_date ? ($employee->joining_date->diffInMonths(now()) % 12) : 0;
        $tenureString = "{$tenureYears}y {$tenureMonths}m";

        $managerName = $employee->reportingManager ? $employee->reportingManager->fullName() : 'Executive Leadership';

        $summary = "{$employee->fullName()} is a {$employee->designation?->name} in the {$employee->department?->name} department, reporting to {$managerName}. "
            . "Joined on " . ($employee->joining_date ? $employee->joining_date->format('M d, Y') : 'Unknown') . " with a tenure of {$tenureString}. "
            . "Currently listed as " . strtoupper($employee->employment_status) . " based in " . ($employee->workLocation?->name ?? 'Headquarters') . ".";

        return [
            'is_advisory' => true,
            'summary' => $summary,
            'confidence' => 0.95,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function processAiInquiry(string $tenantId, string $inquiry): array
    {
        $lower = strtolower($inquiry);

        // Guardrail: Safety checks for adverse or unauthorized employment decisions
        $prohibitedTerms = [
            'terminate', 'fire', 'salary deduction', 'demote', 'penalize', 'adverse action', 'performance rating override'
        ];

        foreach ($prohibitedTerms as $prohibited) {
            if (str_contains($lower, $prohibited)) {
                return [
                    'status' => 'blocked_by_guardrails',
                    'error' => 'Blocked by AI HCM Safety Guardrails: AI cannot evaluate, recommend, or participate in adverse employment, disciplinary, or compensation decisions.',
                ];
            }
        }

        return [
            'status' => 'success',
            'is_advisory' => true,
            'response' => 'Inquiry processed. Showing authorized workforce information and profile structure.',
        ];
    }
}
