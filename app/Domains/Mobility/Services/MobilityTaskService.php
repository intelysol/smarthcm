<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityTask;
use App\Models\User;

class MobilityTaskService
{
    /**
     * Create default lifecycle tasks for an assignment.
     */
    public function generateLifecycleTasks(MobilityAssignment $assignment): array
    {
        $defaultTasks = [
            // Pre-move stage
            ['stage' => 'pre_move', 'assigned_role' => 'compliance_officer', 'title' => 'Verify Visa & Work Permit Approvals'],
            ['stage' => 'pre_move', 'assigned_role' => 'mobility_specialist', 'title' => 'Sign Assignment Letter & Terms'],
            ['stage' => 'pre_move', 'assigned_role' => 'finance', 'title' => 'Establish Intercompany Cost Allocation & Payroll Setup'],

            // Arrival stage
            ['stage' => 'arrival', 'assigned_role' => 'employee', 'title' => 'Host Country Registration & Residence Reporting'],
            ['stage' => 'arrival', 'assigned_role' => 'mobility_specialist', 'title' => 'Host Office Induction & Badge Issuance'],

            // Active stage
            ['stage' => 'active', 'assigned_role' => 'manager', 'title' => 'Mid-Assignment Performance Review & Check-in'],
            ['stage' => 'active', 'assigned_role' => 'finance', 'title' => 'Annual Tax Equalization Calculation & Filing'],

            // Exit stage
            ['stage' => 'exit', 'assigned_role' => 'mobility_specialist', 'title' => 'Repatriation Planning & De-registration'],
            ['stage' => 'exit', 'assigned_role' => 'finance', 'title' => 'Final Expense Claims & Advance Reconciliation'],
        ];

        $tasks = [];
        foreach ($defaultTasks as $taskDef) {
            $task = MobilityTask::create([
                'tenant_id' => $assignment->tenant_id,
                'assignment_id' => $assignment->id,
                'stage' => $taskDef['stage'],
                'title' => $taskDef['title'],
                'assigned_role' => $taskDef['assigned_role'],
                'status' => 'pending',
            ]);
            $tasks[] = $task;
        }

        return $tasks;
    }

    /**
     * Mark a task as completed.
     */
    public function completeTask(MobilityTask $task, ?User $actor = null): MobilityTask
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $actor?->id,
        ]);

        return $task;
    }
}
