<?php

namespace Database\Seeders;

use App\Domains\Onboarding\Models\HcmOnboardingForm;
use App\Domains\Onboarding\Models\HcmOnboardingTemplate;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateTask;
use App\Domains\Onboarding\Models\HcmOnboardingTemplateVersion;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class OnboardingDefaultTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // 1. Digital Forms
            HcmOnboardingForm::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'FORM-EMERGENCY-CONTACT'],
                [
                    'name' => 'Emergency Contact & Medical Details',
                    'schema_definition' => [
                        'fields' => [
                            ['name' => 'primary_contact_name', 'label' => 'Primary Contact Full Name', 'type' => 'text', 'required' => true],
                            ['name' => 'relationship', 'label' => 'Relationship', 'type' => 'text', 'required' => true],
                            ['name' => 'contact_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true],
                            ['name' => 'alternative_phone', 'label' => 'Alternative Phone', 'type' => 'tel', 'required' => false],
                        ],
                    ],
                    'is_active' => true,
                ]
            );

            HcmOnboardingForm::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'FORM-DIRECT-DEPOSIT'],
                [
                    'name' => 'Direct Deposit & Payroll Information',
                    'schema_definition' => [
                        'fields' => [
                            ['name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true],
                            ['name' => 'account_holder_name', 'label' => 'Account Holder Name', 'type' => 'text', 'required' => true],
                            ['name' => 'routing_number', 'label' => 'Routing / Sort Code', 'type' => 'text', 'required' => true],
                            ['name' => 'bank_account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                        ],
                    ],
                    'is_active' => true,
                ]
            );

            // 2. Corporate Standard Template
            $template = HcmOnboardingTemplate::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'TMPL-CORP-STD'],
                [
                    'name' => 'Corporate New Hire Standard',
                    'description' => 'Standard onboarding journey for full-time employees.',
                    'employment_type' => 'full_time',
                    'is_active' => true,
                ]
            );

            $version = HcmOnboardingTemplateVersion::firstOrCreate(
                ['tenant_id' => $tenant->id, 'template_id' => $template->id, 'version_number' => 1],
                ['status' => 'published']
            );

            $tasks = [
                ['title' => 'Review & Sign Employee Handbook', 'task_type' => 'policy', 'owner_role' => 'employee', 'due_offset_days' => -3, 'sla_hours' => 24, 'is_required' => true, 'display_order' => 1],
                ['title' => 'Submit Identity & Proof of Address Documents', 'task_type' => 'document', 'owner_role' => 'employee', 'due_offset_days' => -3, 'sla_hours' => 48, 'is_required' => true, 'display_order' => 2],
                ['title' => 'Complete Direct Deposit Payroll Form', 'task_type' => 'form', 'owner_role' => 'employee', 'due_offset_days' => -2, 'sla_hours' => 24, 'is_required' => true, 'display_order' => 3],
                ['title' => 'Provision Corporate Email & SSO Accounts', 'task_type' => 'it_provisioning', 'owner_role' => 'it', 'due_offset_days' => -1, 'sla_hours' => 12, 'is_required' => true, 'display_order' => 4],
                ['title' => 'Prepare Laptop & Security Hardware Key', 'task_type' => 'equipment', 'owner_role' => 'it', 'due_offset_days' => -1, 'sla_hours' => 12, 'is_required' => true, 'display_order' => 5],
                ['title' => 'Attend Day 1 HR Welcome & Orientation', 'task_type' => 'general', 'owner_role' => 'employee', 'due_offset_days' => 0, 'sla_hours' => 8, 'is_required' => true, 'display_order' => 6],
                ['title' => 'Manager 1-on-1 Welcome & Team Introduction', 'task_type' => 'manager_check', 'owner_role' => 'manager', 'due_offset_days' => 0, 'sla_hours' => 8, 'is_required' => true, 'display_order' => 7],
                ['title' => 'Complete Mandatory Information Security Training', 'task_type' => 'training', 'owner_role' => 'employee', 'due_offset_days' => 7, 'sla_hours' => 40, 'is_required' => true, 'display_order' => 8],
                ['title' => 'Initial 30-Day Check-in & Goal Setting', 'task_type' => 'manager_check', 'owner_role' => 'manager', 'due_offset_days' => 30, 'sla_hours' => 48, 'is_required' => true, 'display_order' => 9],
            ];

            foreach ($tasks as $taskData) {
                HcmOnboardingTemplateTask::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'template_version_id' => $version->id,
                        'title' => $taskData['title'],
                    ],
                    $taskData
                );
            }
        }
    }
}
