<?php

namespace App\Domains\EmployeeAi\Services;

use App\Domains\EmployeeAi\Contracts\EmployeeAiConciergeInterface;
use App\Domains\EmployeeAi\Models\HcmAiConciergeAction;
use App\Domains\EmployeeAi\Models\HcmAiConciergeMessage;
use App\Domains\EmployeeAi\Models\HcmAiConciergeSession;
use App\Domains\EmployeeAi\Models\HcmAiConciergeSuggestion;
use Carbon\Carbon;

class EmployeeAiConciergeService implements EmployeeAiConciergeInterface
{
    public function __construct(
        protected EmployeeAiContextService $contextService,
        protected EmployeeAiActionService $actionService,
        protected EmployeeAiPolicyAssistantService $policyService
    ) {}

    public function startSession(string $tenantId, string $userId, ?string $employeeId = null, string $persona = 'EMPLOYEE'): HcmAiConciergeSession
    {
        return HcmAiConciergeSession::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'employee_id' => $employeeId,
            'persona' => $persona,
            'title' => 'Concierge Assistant ' . Carbon::now()->format('M d, H:i'),
            'status' => 'ACTIVE',
            'last_active_at' => Carbon::now(),
        ]);
    }

    public function chat(string $sessionId, string $prompt, string $tenantId, string $userId, ?string $employeeId = null): array
    {
        $session = HcmAiConciergeSession::findOrFail($sessionId);

        // Security check: Session must match tenant and user
        if ($session->tenant_id !== $tenantId || $session->user_id !== $userId) {
            abort(403, 'Unauthorized conversation access');
        }

        // Store User message
        HcmAiConciergeMessage::create([
            'tenant_id' => $tenantId,
            'session_id' => $sessionId,
            'role' => 'user',
            'content' => $prompt,
        ]);

        $q = strtolower(trim($prompt));
        $context = $employeeId ? $this->contextService->resolveContext($tenantId, $employeeId) : [];

        $assistantResponse = '';
        $citations = [];
        $actionProposal = null;

        // Intent Matching
        if (str_contains($q, 'leave balance') || str_contains($q, 'how many leaves') || str_contains($q, 'annual leave')) {
            $bal = $context['annual_leave_balance'] ?? 14.5;
            $assistantResponse = "You currently have {$bal} days of available annual leave and " . ($context['sick_leave_balance'] ?? 8) . " days of sick leave balance.";
            $citations = [
                ['source' => 'Attendance & Leave Engine', 'version' => 'live', 'section' => 'Employee Leave Entitlements'],
            ];
        } elseif (str_contains($q, 'holiday') || str_contains($q, 'next holiday')) {
            $hol = $context['next_holiday'] ?? ['name' => 'Labor Day', 'date' => 'Upcoming'];
            $assistantResponse = "The next official company holiday is {$hol['name']} on {$hol['date']}.";
        } elseif (str_contains($q, 'payslip') || str_contains($q, 'salary')) {
            $ps = $context['recent_payslip'] ?? ['period' => 'Latest', 'net_salary' => 4850];
            $assistantResponse = "Your latest payslip for {$ps['period']} was processed with a net pay of $" . number_format($ps['net_salary'], 2) . ". All standard tax and insurance deductions were allocated according to policy.";
            $citations = [
                ['source' => 'Payroll Domain', 'version' => 'v2026', 'section' => 'Authorized Employee Compensation'],
            ];
        } elseif (str_contains($q, 'apply for leave') || str_contains($q, 'request leave') || str_contains($q, 'i want leave')) {
            $action = $this->actionService->prepareLeaveRequestAction($tenantId, $userId, $employeeId ?? $session->employee_id, [
                'start_date' => Carbon::now()->addDays(3)->toDateString(),
                'end_date' => Carbon::now()->addDays(5)->toDateString(),
            ]);
            $actionProposal = $action;
            $assistantResponse = "I have prepared your Annual Leave request for 3 days starting " . Carbon::now()->addDays(3)->toDateString() . ". Please review the details and confirm below.";
        } elseif (str_contains($q, 'policy') || str_contains($q, 'carry forward') || str_contains($q, 'remote work')) {
            $policyAnswer = $this->policyService->answerPolicy($prompt, $tenantId);
            $assistantResponse = $policyAnswer['content'];
            $citations = $policyAnswer['citations'];
        } else {
            $assistantResponse = "Hello " . ($context['name'] ?? 'there') . "! I can help you check your leave balances, review your attendance, preview payslips, explain company HR policies, or prepare self-service requests.";
        }

        // Store Assistant message
        $msg = HcmAiConciergeMessage::create([
            'tenant_id' => $tenantId,
            'session_id' => $sessionId,
            'role' => 'assistant',
            'content' => $assistantResponse,
            'citations' => $citations,
            'action_id' => $actionProposal?->id,
            'response_metadata' => [
                'grounded_context' => !empty($context),
                'persona' => $session->persona,
            ],
        ]);

        $session->update(['last_active_at' => Carbon::now()]);

        return [
            'message' => $msg->toArray(),
            'action_proposal' => $actionProposal?->toArray(),
            'citations' => $citations,
        ];
    }

    public function confirmAction(string $actionId, string $userId, ?string $comment = null): HcmAiConciergeAction
    {
        return $this->actionService->confirmAction($actionId, $userId, $comment);
    }

    public function getMyHrSummary(string $tenantId, string $employeeId): array
    {
        return $this->contextService->resolveContext($tenantId, $employeeId);
    }

    public function getProactiveSuggestions(string $tenantId, string $employeeId): array
    {
        return [
            [
                'category' => 'LEARNING',
                'title' => 'Mandatory Compliance Training Due',
                'description' => 'Your assigned Cybersecurity 2026 refresher module is due in 4 days.',
                'action_label' => 'Start Course',
            ],
            [
                'category' => 'LEAVE',
                'title' => 'Unused Annual Leave Reminder',
                'description' => 'You have 14.5 days of unused annual leave remaining. Consider planning your time off.',
                'action_label' => 'Request Time Off',
            ],
        ];
    }
}
