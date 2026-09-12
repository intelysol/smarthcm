<?php

namespace Database\Seeders;

use App\Domains\Recruitment\Models\HcmRecruitmentApplicationStage;
use App\Domains\Recruitment\Models\HcmRecruitmentJobTemplate;
use App\Domains\Recruitment\Models\HcmRecruitmentSource;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class RecruitmentDefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // 1. Sources
            $sources = [
                ['name' => 'Company Career Portal', 'code' => 'CAREER_PORTAL', 'type' => 'career_site'],
                ['name' => 'Employee Referral', 'code' => 'REFERRAL', 'type' => 'referral'],
                ['name' => 'LinkedIn Job Posting', 'code' => 'LINKEDIN', 'type' => 'linkedin'],
                ['name' => 'Indeed / Job Boards', 'code' => 'JOB_BOARD', 'type' => 'job_board'],
                ['name' => 'Executive Search Agency', 'code' => 'AGENCY', 'type' => 'agency'],
                ['name' => 'Direct Outreach', 'code' => 'DIRECT', 'type' => 'direct'],
            ];

            foreach ($sources as $source) {
                HcmRecruitmentSource::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $source['code']],
                    ['name' => $source['name'], 'type' => $source['type'], 'is_active' => true]
                );
            }

            // 2. Standard Stages
            $stages = [
                ['name' => 'New Application', 'code' => 'NEW', 'stage_order' => 1, 'is_system_stage' => true],
                ['name' => 'Resume Screening', 'code' => 'SCREENING', 'stage_order' => 2, 'is_system_stage' => true],
                ['name' => 'Shortlisted', 'code' => 'SHORTLISTED', 'stage_order' => 3, 'is_system_stage' => true],
                ['name' => 'Interviewing', 'code' => 'INTERVIEW', 'stage_order' => 4, 'is_system_stage' => true],
                ['name' => 'Technical Assessment', 'code' => 'ASSESSMENT', 'stage_order' => 5, 'is_system_stage' => true],
                ['name' => 'Offer Extended', 'code' => 'OFFER', 'stage_order' => 6, 'is_system_stage' => true],
                ['name' => 'Hired', 'code' => 'HIRED', 'stage_order' => 7, 'is_system_stage' => true],
            ];

            foreach ($stages as $stage) {
                HcmRecruitmentApplicationStage::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $stage['code']],
                    ['name' => $stage['name'], 'stage_order' => $stage['stage_order'], 'is_system_stage' => $stage['is_system_stage']]
                );
            }

            // 3. Sample Job Template
            HcmRecruitmentJobTemplate::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => 'TMPL-SWE-SR'],
                [
                    'title' => 'Senior Software Engineer',
                    'job_summary' => 'Design, build, and maintain high-throughput backend services and cloud architecture.',
                    'responsibilities' => [
                        'Lead system architecture design and distributed microservices implementation.',
                        'Mentor junior and mid-level software engineers.',
                        'Drive test-driven quality, CI/CD automation, and deployment observability.',
                    ],
                    'required_skills' => ['PHP', 'Laravel', 'PostgreSQL', 'Docker', 'RESTful APIs'],
                    'preferred_skills' => ['Kubernetes', 'Redis', 'AWS', 'Microservices'],
                    'required_experience_years' => 5,
                    'education_level' => 'Bachelor in Computer Science or equivalent',
                    'default_min_salary' => 90000.00,
                    'default_max_salary' => 135000.00,
                    'currency' => 'USD',
                    'is_active' => true,
                ]
            );
        }
    }
}
