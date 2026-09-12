<?php

namespace Database\Seeders;

use App\Domains\EmployeeRelations\Models\EmployeeRelationCaseType;
use App\Domains\EmployeeRelations\Models\EmployeeRelationCorrespondenceTemplate;
use App\Domains\EmployeeRelations\Models\EmployeeRelationPolicyReference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeRelationsCaseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $caseTypes = [
            ['code' => 'GRIEVANCE', 'name' => 'Grievance', 'category' => 'employee_grievance', 'default_priority' => 'normal', 'default_severity' => 'moderate', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'COMPLAINT', 'name' => 'Complaint', 'category' => 'workplace_complaint', 'default_priority' => 'normal', 'default_severity' => 'moderate', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'DISCIPLINARY', 'name' => 'Disciplinary', 'category' => 'disciplinary', 'default_priority' => 'high', 'default_severity' => 'serious', 'default_confidentiality' => 'highly_confidential'],
            ['code' => 'INVESTIGATION', 'name' => 'Investigation', 'category' => 'investigation', 'default_priority' => 'high', 'default_severity' => 'serious', 'default_confidentiality' => 'highly_confidential'],
            ['code' => 'MISCONDUCT', 'name' => 'Misconduct', 'category' => 'misconduct', 'default_priority' => 'high', 'default_severity' => 'serious', 'default_confidentiality' => 'highly_confidential'],
            ['code' => 'HARASSMENT', 'name' => 'Harassment', 'category' => 'harassment', 'default_priority' => 'urgent', 'default_severity' => 'critical', 'default_confidentiality' => 'highly_confidential'],
            ['code' => 'ETHICS', 'name' => 'Ethics Violation', 'category' => 'ethics', 'default_priority' => 'urgent', 'default_severity' => 'serious', 'default_confidentiality' => 'restricted'],
            ['code' => 'CONFLICT', 'name' => 'Workplace Conflict', 'category' => 'conflict', 'default_priority' => 'normal', 'default_severity' => 'minor', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'POLICY_VIOLATION', 'name' => 'Policy Violation', 'category' => 'policy_violation', 'default_priority' => 'normal', 'default_severity' => 'moderate', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'ATTENDANCE', 'name' => 'Attendance Issue', 'category' => 'attendance', 'default_priority' => 'low', 'default_severity' => 'minor', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'PERFORMANCE', 'name' => 'Performance Issue', 'category' => 'performance', 'default_priority' => 'normal', 'default_severity' => 'minor', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'SAFETY', 'name' => 'Workplace Safety', 'category' => 'safety', 'default_priority' => 'urgent', 'default_severity' => 'critical', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'WORKPLACE_CONDUCT', 'name' => 'Workplace Conduct', 'category' => 'workplace_conduct', 'default_priority' => 'normal', 'default_severity' => 'moderate', 'default_confidentiality' => 'standard_confidential'],
            ['code' => 'OTHER', 'name' => 'Other ER Case', 'category' => 'other', 'default_priority' => 'normal', 'default_severity' => 'moderate', 'default_confidentiality' => 'standard_confidential'],
        ];

        foreach ($caseTypes as $index => $ct) {
            EmployeeRelationCaseType::query()->updateOrCreate(
                ['tenant_id' => null, 'code' => $ct['code']],
                [
                    'name' => $ct['name'],
                    'description' => "Standard {$ct['name']} case classification.",
                    'category' => $ct['category'],
                    'default_priority' => $ct['default_priority'],
                    'default_severity' => $ct['default_severity'],
                    'default_confidentiality' => $ct['default_confidentiality'],
                    'is_anonymous_allowed' => in_array($ct['code'], ['GRIEVANCE', 'COMPLAINT', 'HARASSMENT', 'ETHICS', 'SAFETY', 'OTHER'], true),
                    'is_self_service_allowed' => in_array($ct['code'], ['GRIEVANCE', 'COMPLAINT', 'HARASSMENT', 'CONFLICT', 'POLICY_VIOLATION', 'SAFETY', 'OTHER'], true),
                    'is_system' => true,
                    'status' => 'active',
                    'sort_order' => $index,
                ]
            );
        }

        // Default Correspondence Templates
        $templates = [
            [
                'code' => 'CASE_ACKNOWLEDGEMENT',
                'name' => 'Case Intake Acknowledgement',
                'template_type' => 'case_acknowledgement',
                'subject_template' => 'Case Intake Confirmation: {case_number}',
                'body_template' => 'Dear {recipient_name},\n\nWe acknowledge receipt of your case ({case_number}) titled "{case_title}". A designated ER representative has been assigned.\n\nFlow HCM Employee Relations',
            ],
            [
                'code' => 'INTERVIEW_INVITATION',
                'name' => 'Investigation Interview Invitation',
                'template_type' => 'interview_invitation',
                'subject_template' => 'Confidential: Interview Notice for Case {case_number}',
                'body_template' => 'Dear {recipient_name},\n\nYou are requested to attend a confidential interview regarding case {case_number} on {scheduled_at} at {location}.\n\nFlow HCM Employee Relations',
            ],
            [
                'code' => 'HEARING_NOTICE',
                'name' => 'Formal Hearing Notice',
                'template_type' => 'hearing_notice',
                'subject_template' => 'Formal Hearing Notice: Case {case_number}',
                'body_template' => 'Dear {recipient_name},\n\nA formal case hearing has been scheduled for {case_number} on {scheduled_at}.\n\nFlow HCM Employee Relations',
            ],
            [
                'code' => 'OUTCOME_NOTICE',
                'name' => 'Case Decision Outcome Notice',
                'template_type' => 'outcome_notice',
                'subject_template' => 'Case Decision Notification: {case_number}',
                'body_template' => 'Dear {recipient_name},\n\nA formal decision has been recorded for case {case_number}. Please access your employee relations portal to view details and required acknowledgements.\n\nFlow HCM Employee Relations',
            ],
            [
                'code' => 'WARNING_NOTICE',
                'name' => 'Formal Written Warning Notice',
                'template_type' => 'warning',
                'subject_template' => 'Official Notice: Written Warning ({case_number})',
                'body_template' => 'Dear {recipient_name},\n\nThis communication serves as formal notice of a written warning regarding {case_number}.\n\nFlow HCM Employee Relations',
            ],
            [
                'code' => 'APPEAL_ACKNOWLEDGEMENT',
                'name' => 'Appeal Submission Acknowledgement',
                'template_type' => 'appeal_acknowledgement',
                'subject_template' => 'Appeal Received: Case {case_number} (Appeal {appeal_number})',
                'body_template' => 'Dear {recipient_name},\n\nYour appeal for case {case_number} has been received under appeal reference {appeal_number}.\n\nFlow HCM Employee Relations',
            ],
            [
                'code' => 'CASE_CLOSURE',
                'name' => 'Case Closure Notice',
                'template_type' => 'case_closure',
                'subject_template' => 'Case Closed: {case_number}',
                'body_template' => 'Dear {recipient_name},\n\nCase {case_number} has been formally closed following completion of all required actions.\n\nFlow HCM Employee Relations',
            ],
        ];

        foreach ($templates as $tmpl) {
            EmployeeRelationCorrespondenceTemplate::query()->updateOrCreate(
                ['tenant_id' => null, 'code' => $tmpl['code']],
                [
                    'name' => $tmpl['name'],
                    'template_type' => $tmpl['template_type'],
                    'subject_template' => $tmpl['subject_template'],
                    'body_template' => $tmpl['body_template'],
                    'is_active' => true,
                ]
            );
        }

        // Default Policy References
        $policies = [
            ['policy_code' => 'POL-COC-01', 'policy_name' => 'Code of Business Conduct & Ethics', 'category' => 'code_of_conduct', 'section_clause' => 'Section 3.2 - Workplace Conduct'],
            ['policy_code' => 'POL-HAR-01', 'policy_name' => 'Anti-Harassment & Equal Opportunity Policy', 'category' => 'hr_policy', 'section_clause' => 'Section 2.1 - Zero Tolerance'],
            ['policy_code' => 'POL-ATT-01', 'policy_name' => 'Employee Attendance & Punctuality Policy', 'category' => 'attendance_policy', 'section_clause' => 'Section 4.0 - Absence Guidelines'],
            ['policy_code' => 'POL-SAF-01', 'policy_name' => 'Occupational Health & Safety Policy', 'category' => 'safety_policy', 'section_clause' => 'Section 1.5 - Hazard Reporting'],
        ];

        foreach ($policies as $pol) {
            EmployeeRelationPolicyReference::query()->updateOrCreate(
                ['tenant_id' => null, 'policy_code' => $pol['policy_code']],
                [
                    'policy_name' => $pol['policy_name'],
                    'category' => $pol['category'],
                    'section_clause' => $pol['section_clause'],
                    'is_active' => true,
                ]
            );
        }
    }
}
