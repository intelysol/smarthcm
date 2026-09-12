<?php

namespace Database\Seeders;

use App\Domains\SelfService\Models\HrAnnouncement;
use App\Domains\SelfService\Models\HrAnnouncementAudience;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeCategory;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceFormDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceSlaPolicy;
use App\Domains\SelfService\Models\HrServiceTemplate;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class SelfServiceDefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $this->seedTenantData($tenant->id);
        }
    }

    public function seedTenantData(string $tenantId): void
    {
        // 1. SLA Policies
        $standardSla = HrServiceSlaPolicy::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'SLA-STANDARD'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Standard HR Service SLA',
                'description' => '4 hours first response, 48 hours resolution during business days.',
                'response_time_minutes' => 240,
                'resolution_time_minutes' => 2880,
                'use_business_hours' => true,
                'is_active' => true,
            ]
        );

        $urgentSla = HrServiceSlaPolicy::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'SLA-URGENT'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Urgent Query SLA',
                'description' => '1 hour first response, 8 hours resolution.',
                'response_time_minutes' => 60,
                'resolution_time_minutes' => 480,
                'use_business_hours' => true,
                'is_active' => true,
            ]
        );

        // 2. Service Queues
        $hrOpsQueue = HrServiceQueue::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'QUEUE-HROPS'],
            [
                'tenant_id' => $tenantId,
                'name' => 'HR Operations Team',
                'description' => 'General employee requests, letters, profile changes, and document verification.',
                'assignment_method' => 'round_robin',
                'is_active' => true,
            ]
        );

        $payrollQueue = HrServiceQueue::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'QUEUE-PAYROLL'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Payroll & Compensation Team',
                'description' => 'Payroll inquiries, salary slips, tax deductions, and bank updates.',
                'assignment_method' => 'workload_based',
                'is_active' => true,
            ]
        );

        // 3. Service Categories
        $categories = [
            ['code' => 'CAT-DOCS', 'name' => 'HR Letters & Certificates', 'icon' => 'fa-file-certificate', 'display_order' => 1],
            ['code' => 'CAT-PROFILE', 'name' => 'Employee Profile & Info', 'icon' => 'fa-user-gear', 'display_order' => 2],
            ['code' => 'CAT-PAYROLL', 'name' => 'Payroll & Compensation', 'icon' => 'fa-money-bill-wave', 'display_order' => 3],
            ['code' => 'CAT-TIME', 'name' => 'Attendance & Leave Queries', 'icon' => 'fa-calendar-check', 'display_order' => 4],
            ['code' => 'CAT-BENEFITS', 'name' => 'Benefits & Insurance', 'icon' => 'fa-shield-heart', 'display_order' => 5],
            ['code' => 'CAT-GENERAL', 'name' => 'General HR Inquiries', 'icon' => 'fa-circle-question', 'display_order' => 6],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['code']] = HrServiceCategory::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $cat['code']],
                array_merge($cat, ['tenant_id' => $tenantId, 'is_active' => true])
            );
        }

        // 4. Service Definitions & Forms
        // A. Employment Certificate
        $empCertService = HrServiceDefinition::updateOrCreate(
            ['tenant_id' => $tenantId, 'service_code' => 'SVC-EMP-CERT'],
            [
                'tenant_id' => $tenantId,
                'hr_service_category_id' => $categoryModels['CAT-DOCS']->id,
                'name' => 'Employment Verification Certificate',
                'description' => 'Official certificate confirming your active employment, job designation, and joining date.',
                'icon' => 'fa-certificate',
                'audience' => 'all_employees',
                'default_queue_id' => $hrOpsQueue->id,
                'sla_policy_id' => $standardSla->id,
                'current_version' => 1,
                'requires_approval' => false,
                'requires_employee_acknowledgement' => true,
                'is_popular' => true,
                'status' => 'active',
            ]
        );

        $empCertVersion = HrServiceVersion::updateOrCreate(
            ['tenant_id' => $tenantId, 'hr_service_definition_id' => $empCertService->id, 'version_number' => 1],
            [
                'tenant_id' => $tenantId,
                'effective_from' => '2026-01-01',
                'change_summary' => 'Initial standard version',
                'is_active' => true,
            ]
        );

        HrServiceFormDefinition::updateOrCreate(
            ['tenant_id' => $tenantId, 'hr_service_version_id' => $empCertVersion->id],
            [
                'form_name' => 'Employment Certificate Form',
                'schema' => [
                    [
                        'key' => 'purpose',
                        'label' => 'Purpose of Certificate',
                        'type' => 'select',
                        'required' => true,
                        'options' => ['Visa / Embassy Application', 'Bank Loan / Credit Card', 'Higher Education', 'Other Legal Matter'],
                    ],
                    [
                        'key' => 'addressee_organization',
                        'label' => 'Addressed To (e.g. Embassy of France / Standard Chartered Bank)',
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'key' => 'required_by_date',
                        'label' => 'Required By Date',
                        'type' => 'date',
                        'required' => false,
                    ],
                    [
                        'key' => 'additional_notes',
                        'label' => 'Additional Remarks or Special Requirements',
                        'type' => 'textarea',
                        'required' => false,
                    ],
                ],
            ]
        );

        // Document Template for Employment Certificate
        HrServiceTemplate::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'TPL-EMP-CERT'],
            [
                'tenant_id' => $tenantId,
                'hr_service_definition_id' => $empCertService->id,
                'name' => 'Standard Employment Certificate Template',
                'template_type' => 'employment_certificate',
                'template_body' => "TO WHOM IT MAY CONCERN\n\nThis is to certify that {{employee.name}} (Employee ID: {{employee.code}}) is currently employed with us as {{employee.position}} in the {{employee.department}} department since {{employee.joining_date}}.\n\nThis certificate is issued at the request of the employee for {{request.purpose}} without any legal liability on the company.\n\nSincerely,\nHuman Resources Department\nFlow HCM Enterprise",
                'placeholders' => ['employee.name', 'employee.code', 'employee.position', 'employee.department', 'employee.joining_date', 'request.purpose'],
                'requires_approval' => true,
                'is_active' => true,
            ]
        );

        // B. Address Change Request
        $addressService = HrServiceDefinition::updateOrCreate(
            ['tenant_id' => $tenantId, 'service_code' => 'SVC-ADDR-CHANGE'],
            [
                'tenant_id' => $tenantId,
                'hr_service_category_id' => $categoryModels['CAT-PROFILE']->id,
                'name' => 'Residential Address Change',
                'description' => 'Update your permanent and current residential address in official records.',
                'icon' => 'fa-house-user',
                'default_queue_id' => $hrOpsQueue->id,
                'sla_policy_id' => $standardSla->id,
                'current_version' => 1,
                'is_popular' => false,
                'status' => 'active',
            ]
        );

        $addressVersion = HrServiceVersion::updateOrCreate(
            ['tenant_id' => $tenantId, 'hr_service_definition_id' => $addressService->id, 'version_number' => 1],
            ['tenant_id' => $tenantId, 'effective_from' => '2026-01-01', 'is_active' => true]
        );

        HrServiceFormDefinition::updateOrCreate(
            ['tenant_id' => $tenantId, 'hr_service_version_id' => $addressVersion->id],
            [
                'form_name' => 'Address Update Form',
                'schema' => [
                    ['key' => 'new_address', 'label' => 'New Residential Address', 'type' => 'textarea', 'required' => true],
                    ['key' => 'city', 'label' => 'City', 'type' => 'text', 'required' => true],
                    ['key' => 'postal_code', 'label' => 'Postal Code', 'type' => 'text', 'required' => false],
                    ['key' => 'effective_date', 'label' => 'Effective Date', 'type' => 'date', 'required' => true],
                ],
            ]
        );

        // 5. Knowledge Base
        $kbCat = HrKnowledgeCategory::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'KB-GENERAL'],
            ['tenant_id' => $tenantId, 'name' => 'Company Policies & General HR', 'icon' => 'fa-book-open', 'display_order' => 1, 'is_active' => true]
        );

        HrKnowledgeArticle::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => 'how-to-request-hr-certificates'],
            [
                'tenant_id' => $tenantId,
                'hr_knowledge_category_id' => $kbCat->id,
                'hr_service_definition_id' => $empCertService->id,
                'title' => 'How to Request Official HR Letters and Employment Certificates',
                'summary' => 'Step-by-step guide to generating and receiving signed employment certificates online.',
                'content' => "Employees can request employment certificates directly through the HR Service Portal.\n1. Navigate to HR Service Catalog -> HR Letters.\n2. Choose 'Employment Verification Certificate'.\n3. Specify the addressee and purpose.\n4. Submit the request. It will be verified by HR Operations within 2 business days.\n5. Once approved, the signed certificate is downloadable under 'My Documents'.",
                'keywords' => ['employment letter', 'certificate', 'visa', 'bank loan', 'experience letter'],
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        // 6. Announcements
        $announcement = HrAnnouncement::updateOrCreate(
            ['tenant_id' => $tenantId, 'title' => 'Annual Open Enrollment & HR Portal 2026 Launched'],
            [
                'tenant_id' => $tenantId,
                'category' => 'general',
                'content' => 'Welcome to the unified Flow HCM Employee Self-Service and HR Service Desk. You can now submit service requests, download certificates, and view announcements directly.',
                'priority' => 'high',
                'requires_acknowledgement' => true,
                'published_at' => now(),
                'status' => 'published',
            ]
        );

        HrAnnouncementAudience::updateOrCreate(
            ['tenant_id' => $tenantId, 'hr_announcement_id' => $announcement->id, 'audience_type' => 'tenant'],
            ['tenant_id' => $tenantId, 'audience_id' => null]
        );
    }
}
