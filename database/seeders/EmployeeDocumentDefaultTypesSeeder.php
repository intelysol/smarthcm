<?php

namespace Database\Seeders;

use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class EmployeeDocumentDefaultTypesSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $categories = [
            ['code' => 'PERSONAL', 'name' => 'Personal Documents', 'icon' => 'fa-user', 'sort' => 1],
            ['code' => 'IDENTITY', 'name' => 'Identity & Civil Status', 'icon' => 'fa-id-card', 'sort' => 2],
            ['code' => 'EMPLOYMENT', 'name' => 'Employment & Position', 'icon' => 'fa-briefcase', 'sort' => 3],
            ['code' => 'CONTRACT', 'name' => 'Contracts & Amendments', 'icon' => 'fa-file-signature', 'sort' => 4],
            ['code' => 'COMPENSATION', 'name' => 'Compensation & Salary', 'icon' => 'fa-money-bill-wave', 'sort' => 5],
            ['code' => 'TAX', 'name' => 'Tax & Statutory Filings', 'icon' => 'fa-receipt', 'sort' => 6],
            ['code' => 'BENEFITS', 'name' => 'Benefits & Healthcare', 'icon' => 'fa-heart-pulse', 'sort' => 7],
            ['code' => 'INSURANCE', 'name' => 'Insurance Policies', 'icon' => 'fa-shield-halved', 'sort' => 8],
            ['code' => 'RETIREMENT', 'name' => 'Pension & Retirement', 'icon' => 'fa-piggy-bank', 'sort' => 9],
            ['code' => 'LEARNING', 'name' => 'Learning & Education', 'icon' => 'fa-graduation-cap', 'sort' => 10],
            ['code' => 'CERTIFICATION', 'name' => 'Professional Licenses & Certs', 'icon' => 'fa-certificate', 'sort' => 11],
            ['code' => 'PERFORMANCE', 'name' => 'Performance & Appraisals', 'icon' => 'fa-chart-line', 'sort' => 12],
            ['code' => 'TALENT', 'name' => 'Talent & Succession', 'icon' => 'fa-star', 'sort' => 13],
            ['code' => 'ATTENDANCE', 'name' => 'Attendance Records', 'icon' => 'fa-calendar-check', 'sort' => 14],
            ['code' => 'LEAVE', 'name' => 'Leave Records & Approvals', 'icon' => 'fa-calendar-xmark', 'sort' => 15],
            ['code' => 'EMPLOYEE_RELATIONS', 'name' => 'Employee Relations & Grievances', 'icon' => 'fa-scale-balanced', 'sort' => 16],
            ['code' => 'HR_SERVICE', 'name' => 'HR Service Requests', 'icon' => 'fa-headset', 'sort' => 17],
            ['code' => 'ONBOARDING', 'name' => 'Onboarding & Joining Forms', 'icon' => 'fa-door-open', 'sort' => 18],
            ['code' => 'LIFECYCLE', 'name' => 'Lifecycle & Job Changes', 'icon' => 'fa-arrows-rotate', 'sort' => 19],
            ['code' => 'OFFBOARDING', 'name' => 'Offboarding & Exit Clearances', 'icon' => 'fa-arrow-right-from-bracket', 'sort' => 20],
            ['code' => 'COMPLIANCE', 'name' => 'Compliance & Work Permits', 'icon' => 'fa-shield-check', 'sort' => 21],
            ['code' => 'OTHER', 'name' => 'Miscellaneous Records', 'icon' => 'fa-folder', 'sort' => 22],
        ];

        foreach ($tenants as $tenant) {
            $catMap = [];
            foreach ($categories as $cat) {
                $createdCat = HcmDocumentCategory::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $cat['code']],
                    [
                        'name' => $cat['name'],
                        'icon' => $cat['icon'],
                        'sort_order' => $cat['sort'],
                        'is_active' => true,
                    ]
                );
                $catMap[$cat['code']] = $createdCat->id;
            }

            // Core Document Types
            $types = [
                ['cat' => 'IDENTITY', 'code' => 'CNIC', 'name' => 'National Identity Card (CNIC)', 'expires' => true, 'verification' => true],
                ['cat' => 'IDENTITY', 'code' => 'PASSPORT', 'name' => 'International Passport', 'expires' => true, 'verification' => true],
                ['cat' => 'IDENTITY', 'code' => 'DRIVING_LICENSE', 'name' => 'Driving License', 'expires' => true, 'verification' => true],
                ['cat' => 'CONTRACT', 'code' => 'EMPLOYMENT_CONTRACT', 'name' => 'Employment Contract', 'expires' => false, 'verification' => true, 'ack' => true],
                ['cat' => 'EMPLOYMENT', 'code' => 'OFFER_LETTER', 'name' => 'Job Offer Letter', 'expires' => false, 'verification' => true],
                ['cat' => 'LEARNING', 'code' => 'DEGREE_CERTIFICATE', 'name' => 'University Degree Certificate', 'expires' => false, 'verification' => true],
                ['cat' => 'CERTIFICATION', 'code' => 'PROFESSIONAL_LICENSE', 'name' => 'Professional Practice License', 'expires' => true, 'verification' => true],
                ['cat' => 'COMPENSATION', 'code' => 'PAYSLIP', 'name' => 'Monthly Payslip', 'expires' => false, 'verification' => false],
                ['cat' => 'LIFECYCLE', 'code' => 'PROMOTION_LETTER', 'name' => 'Promotion Notification Letter', 'expires' => false, 'verification' => true],
                ['cat' => 'OFFBOARDING', 'code' => 'RELIEVING_LETTER', 'name' => 'Relieving Certificate', 'expires' => false, 'verification' => true],
            ];

            foreach ($types as $typeData) {
                if (isset($catMap[$typeData['cat']])) {
                    HcmDocumentType::firstOrCreate(
                        ['tenant_id' => $tenant->id, 'code' => $typeData['code']],
                        [
                            'category_id' => $catMap[$typeData['cat']],
                            'name' => $typeData['name'],
                            'requires_verification' => $typeData['verification'],
                            'requires_acknowledgement' => $typeData['ack'] ?? false,
                            'expires' => $typeData['expires'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
