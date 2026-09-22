<?php

namespace Database\Seeders;

use App\Domains\Attendance\Models\WorkSchedule;
use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\BusinessUnit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Department;
use App\Domains\SelfService\Enums\ServiceRequestPriority;
use App\Domains\SelfService\Enums\ServiceRequestStatus;
use App\Domains\SelfService\Models\HrKnowledgeArticle;
use App\Domains\SelfService\Models\HrKnowledgeCategory;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceQueue;
use App\Domains\SelfService\Models\HrServiceQueueMember;
use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceRequestComment;
use App\Domains\SelfService\Models\HrServiceSlaInstance;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrServiceDeliveryDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'smarthcm-enterprise'],
            [
                'name' => 'SmartHCM Enterprise Global',
                'timezone' => 'UTC',
                'currency' => 'USD',
            ]
        );

        // 2. Company & Structure
        $company = Company::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'SmartHCM Corporation'],
            [
                'legal_name' => 'SmartHCM Corporation International LLC',
                'timezone' => 'UTC',
                'currency' => 'USD',
            ]
        );

        $bu = BusinessUnit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'BU-GLOBAL'],
            [
                'company_id' => $company->id,
                'name' => 'Global Technology & Services',
            ]
        );

        $dept = Department::firstOrCreate(
            ['tenant_id' => $tenant->id, 'department_code' => 'DEP-HR-OPS'],
            [
                'business_unit_id' => $bu->id,
                'department_name' => 'People Operations & Service Delivery',
            ]
        );

        // 3. HR Admin User & Lead Agent
        $hrUser = User::firstOrCreate(
            ['email' => 'sarah.jenkins@smarthcm.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Sarah Jenkins',
                'password' => Hash::make('password'),
            ]
        );

        $hrEmp = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_code' => 'EMP-HR-001'],
            [
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'user_id' => $hrUser->id,
                'employee_number' => 'EMP-HR-001',
                'first_name' => 'Sarah',
                'last_name' => 'Jenkins',
                'gender' => 'female',
                'date_of_birth' => '1988-04-12',
                'joining_date' => '2021-01-15',
                'employment_status' => 'active',
            ]
        );

        // 4. Employee User (Requester)
        $employeeUser = User::firstOrCreate(
            ['email' => 'jordan.lee@smarthcm.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Jordan Lee',
                'password' => Hash::make('password'),
            ]
        );

        $employee = Employee::firstOrCreate(
            ['tenant_id' => $tenant->id, 'employee_code' => 'EMP-ENG-102'],
            [
                'company_id' => $company->id,
                'department_id' => $dept->id,
                'user_id' => $employeeUser->id,
                'employee_number' => 'EMP-ENG-102',
                'first_name' => 'Jordan',
                'last_name' => 'Lee',
                'gender' => 'non_binary',
                'date_of_birth' => '1994-08-20',
                'joining_date' => '2022-03-01',
                'employment_status' => 'active',
            ]
        );

        // 5. Operational Queues
        $qOps = HrServiceQueue::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'QUEUE-HR-OPS'],
            [
                'name' => 'HR Operations & Lifecycle',
                'description' => 'General queries, letters, verification, and personnel record updates.',
                'email' => 'hrops@smarthcm.com',
                'max_capacity' => 20,
                'is_active' => true,
            ]
        );

        $qPayroll = HrServiceQueue::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'QUEUE-PAYROLL'],
            [
                'name' => 'Payroll & Compensation',
                'description' => 'Direct deposit changes, tax withholding, payslip inquiries, and bonus reviews.',
                'email' => 'payroll@smarthcm.com',
                'max_capacity' => 15,
                'is_active' => true,
            ]
        );

        $qBenefits = HrServiceQueue::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'QUEUE-BENEFITS'],
            [
                'name' => 'Health & Welfare Benefits',
                'description' => 'Medical, dental, life insurance claims, and retirement plan support.',
                'email' => 'benefits@smarthcm.com',
                'max_capacity' => 15,
                'is_active' => true,
            ]
        );

        // Queue Memberships
        HrServiceQueueMember::firstOrCreate(
            ['queue_id' => $qOps->id, 'user_id' => $hrUser->id],
            ['role' => 'lead', 'is_active' => true]
        );
        HrServiceQueueMember::firstOrCreate(
            ['queue_id' => $qPayroll->id, 'user_id' => $hrUser->id],
            ['role' => 'member', 'is_active' => true]
        );

        // 6. Service Categories & Catalog Items
        $catGeneral = HrServiceCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'CAT-GEN'],
            ['name' => 'General Workforce Services', 'is_active' => true]
        );
        $catFinance = HrServiceCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'CAT-PAY'],
            ['name' => 'Payroll & Rewards', 'is_active' => true]
        );

        $srvCert = HrServiceDefinition::firstOrCreate(
            ['tenant_id' => $tenant->id, 'service_code' => 'SRV-CERT'],
            [
                'category_id' => $catGeneral->id,
                'title' => 'Employment Verification Letter',
                'name' => 'Employment Verification Letter',
                'description' => 'Official letter confirming current employment, title, and tenure for visa, bank, or housing applications.',
                'default_queue_id' => $qOps->id,
                'sla_hours' => 24,
                'is_active' => true,
                'published_at' => Carbon::now(),
            ]
        );

        $srvBank = HrServiceDefinition::firstOrCreate(
            ['tenant_id' => $tenant->id, 'service_code' => 'SRV-BANK'],
            [
                'category_id' => $catFinance->id,
                'title' => 'Payroll Direct Deposit Update',
                'name' => 'Payroll Direct Deposit Update',
                'description' => 'Submit revised bank account and routing details for monthly direct salary disbursement.',
                'default_queue_id' => $qPayroll->id,
                'sla_hours' => 48,
                'is_active' => true,
                'published_at' => Carbon::now(),
            ]
        );

        $srvMedical = HrServiceDefinition::firstOrCreate(
            ['tenant_id' => $tenant->id, 'service_code' => 'SRV-MED'],
            [
                'category_id' => $catGeneral->id,
                'title' => 'Health Insurance Card Replacement',
                'name' => 'Health Insurance Card Replacement',
                'description' => 'Request a replacement medical insurance card or add an authorized dependent.',
                'default_queue_id' => $qBenefits->id,
                'sla_hours' => 72,
                'is_active' => true,
                'published_at' => Carbon::now(),
            ]
        );

        // 7. Seed Cases with realistic operational statuses & SLAs
        $case1 = HrServiceRequest::firstOrCreate(
            ['tenant_id' => $tenant->id, 'request_number' => 'REQ-2026-0001'],
            [
                'employee_id' => $employee->id,
                'hr_service_definition_id' => $srvCert->id,
                'assigned_queue_id' => $qOps->id,
                'assigned_user_id' => $hrUser->id,
                'subject' => 'Employment letter for Schengen Visa application',
                'description' => 'Urgent letter required stating annual compensation and active employment status for upcoming European business conference.',
                'priority' => ServiceRequestPriority::HIGH->value,
                'status' => ServiceRequestStatus::IN_PROGRESS->value,
                'due_at' => Carbon::now()->addHours(12),
                'created_at' => Carbon::now()->subHours(4),
            ]
        );

        $case2 = HrServiceRequest::firstOrCreate(
            ['tenant_id' => $tenant->id, 'request_number' => 'REQ-2026-0002'],
            [
                'employee_id' => $employee->id,
                'hr_service_definition_id' => $srvBank->id,
                'assigned_queue_id' => $qPayroll->id,
                'assigned_user_id' => null,
                'subject' => 'Revised IBAN for salary direct deposit',
                'description' => 'Switched commercial banking institution. Attached revised IBAN statement for upcoming month-end payroll cutoff.',
                'priority' => ServiceRequestPriority::URGENT->value,
                'status' => ServiceRequestStatus::SUBMITTED->value,
                'due_at' => Carbon::now()->subHours(2), // Overdue SLA breach
                'created_at' => Carbon::now()->subHours(26),
            ]
        );

        $case3 = HrServiceRequest::firstOrCreate(
            ['tenant_id' => $tenant->id, 'request_number' => 'REQ-2026-0003'],
            [
                'employee_id' => $employee->id,
                'hr_service_definition_id' => $srvMedical->id,
                'assigned_queue_id' => $qBenefits->id,
                'assigned_user_id' => $hrUser->id,
                'subject' => 'Dependent healthcare enrollment query',
                'description' => 'Need instructions on submitting newborn birth certificate for healthcare policy addition.',
                'priority' => ServiceRequestPriority::MEDIUM->value,
                'status' => ServiceRequestStatus::WAITING_ON_EMPLOYEE->value,
                'due_at' => Carbon::now()->addHours(36),
                'created_at' => Carbon::now()->subDays(1),
            ]
        );

        $case4 = HrServiceRequest::firstOrCreate(
            ['tenant_id' => $tenant->id, 'request_number' => 'REQ-2026-0004'],
            [
                'employee_id' => $employee->id,
                'hr_service_definition_id' => $srvCert->id,
                'assigned_queue_id' => $qOps->id,
                'assigned_user_id' => $hrUser->id,
                'subject' => 'Tenure confirmation for bank auto loan',
                'description' => 'Bank requires verification of permanent employment status.',
                'priority' => ServiceRequestPriority::LOW->value,
                'status' => ServiceRequestStatus::RESOLVED->value,
                'due_at' => Carbon::now()->subDays(2),
                'resolved_at' => Carbon::now()->subDays(1),
                'resolution_notes' => 'Generated digitally signed employment confirmation certificate and emailed directly to employee loan officer.',
                'created_at' => Carbon::now()->subDays(3),
            ]
        );

        // Case SLA instances
        HrServiceSlaInstance::firstOrCreate(
            ['request_id' => $case1->id],
            [
                'tenant_id' => $tenant->id,
                'resolution_due_at' => Carbon::now()->addHours(12),
                'is_breached' => false,
            ]
        );

        HrServiceSlaInstance::firstOrCreate(
            ['request_id' => $case2->id],
            [
                'tenant_id' => $tenant->id,
                'resolution_due_at' => Carbon::now()->subHours(2),
                'is_breached' => true,
            ]
        );

        // Timeline events (public & confidential internal notes)
        HrServiceRequestComment::firstOrCreate(
            ['request_id' => $case1->id, 'comment_type' => 'internal'],
            [
                'tenant_id' => $tenant->id,
                'user_id' => $hrUser->id,
                'message' => 'Compensation letter generated with VP approval. Verifying Embassy letterhead template before sending.',
                'is_internal' => true,
                'created_at' => Carbon::now()->subHours(2),
            ]
        );

        HrServiceRequestComment::firstOrCreate(
            ['request_id' => $case1->id, 'comment_type' => 'public'],
            [
                'tenant_id' => $tenant->id,
                'user_id' => $hrUser->id,
                'message' => 'Hello Jordan, we have reviewed your travel dates and your verified letter is being finalized today.',
                'is_internal' => false,
                'created_at' => Carbon::now()->subHour(),
            ]
        );

        // 8. Knowledge Articles (for Self-Service Deflection)
        $knowCat = HrKnowledgeCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'KNOW-LEAVE'],
            ['name' => 'Leave, Attendance & Remote Policies', 'is_active' => true]
        );

        HrKnowledgeArticle::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'parental-leave-policy-2026'],
            [
                'category_id' => $knowCat->id,
                'title' => 'Global Parental Leave Policy & Benefit Entitlements',
                'summary' => 'Comprehensive guidelines regarding maternity, paternity, and adoption leave allowances.',
                'content' => '<p>All full-time employees are eligible for up to 16 weeks of fully paid parental leave within the first 12 months following birth or adoption.</p>',
                'views_count' => 1420,
                'helpful_count' => 380,
                'not_helpful_count' => 12,
                'is_published' => true,
                'published_at' => Carbon::now()->subMonths(6),
            ]
        );

        HrKnowledgeArticle::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'expense-reimbursement-guidelines'],
            [
                'category_id' => $knowCat->id,
                'title' => 'Travel & Business Expense Reimbursement Procedures',
                'summary' => 'Per diem limits, receipt requirements, and expense submission deadlines for corporate travel.',
                'content' => '<p>Submit all travel-related receipts within 30 days of trip conclusion using the Self-Service expenses portal.</p>',
                'views_count' => 980,
                'helpful_count' => 245,
                'not_helpful_count' => 8,
                'is_published' => true,
                'published_at' => Carbon::now()->subMonths(4),
            ]
        );
    }
}
