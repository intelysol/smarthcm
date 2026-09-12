<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SelfServicePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Service Catalog
            ['code' => 'hcm.services.view', 'name' => 'View Services', 'module' => 'self_service', 'description' => 'View HR service catalog and categories'],
            ['code' => 'hcm.services.manage', 'name' => 'Manage Services', 'module' => 'self_service', 'description' => 'Create and configure HR services and dynamic form definitions'],

            // Service Requests
            ['code' => 'hcm.requests.view', 'name' => 'View Requests', 'module' => 'self_service', 'description' => 'View HR service requests and ticket history'],
            ['code' => 'hcm.requests.manage', 'name' => 'Manage Requests', 'module' => 'self_service', 'description' => 'Create and update HR service requests'],
            ['code' => 'hcm.requests.assign', 'name' => 'Assign Requests', 'module' => 'self_service', 'description' => 'Assign requests to HR queues and agents'],
            ['code' => 'hcm.requests.reassign', 'name' => 'Reassign Requests', 'module' => 'self_service', 'description' => 'Reassign requests to another queue or specialist'],
            ['code' => 'hcm.requests.comment', 'name' => 'Comment on Requests', 'module' => 'self_service', 'description' => 'Post public communication comments on requests'],
            ['code' => 'hcm.requests.internal_note', 'name' => 'Manage Internal Notes', 'module' => 'self_service', 'description' => 'Post and view confidential internal notes visible only to HR agents'],

            // Lifecycle & Resolution
            ['code' => 'hcm.requests.approve', 'name' => 'Approve Requests', 'module' => 'self_service', 'description' => 'Approve or reject service requests requiring manager/HR approval'],
            ['code' => 'hcm.requests.resolve', 'name' => 'Resolve Requests', 'module' => 'self_service', 'description' => 'Mark service requests as resolved and provide resolution summary'],
            ['code' => 'hcm.requests.close', 'name' => 'Close Requests', 'module' => 'self_service', 'description' => 'Perform final closure on resolved requests'],
            ['code' => 'hcm.requests.reopen', 'name' => 'Reopen Requests', 'module' => 'self_service', 'description' => 'Reopen resolved or closed service requests'],

            // SLA & Escalation
            ['code' => 'hcm.requests.sla.manage', 'name' => 'Manage SLA Policies', 'module' => 'self_service', 'description' => 'Configure response and resolution SLA policies'],
            ['code' => 'hcm.requests.escalate', 'name' => 'Escalate Requests', 'module' => 'self_service', 'description' => 'Escalate service requests to team leads or HR managers'],

            // Knowledge Base
            ['code' => 'hcm.knowledge.view', 'name' => 'View Knowledge Base', 'module' => 'self_service', 'description' => 'Browse and read knowledge articles and FAQs'],
            ['code' => 'hcm.knowledge.manage', 'name' => 'Manage Knowledge Base', 'module' => 'self_service', 'description' => 'Draft and edit knowledge articles and categories'],
            ['code' => 'hcm.knowledge.publish', 'name' => 'Publish Knowledge Base', 'module' => 'self_service', 'description' => 'Publish knowledge articles to the employee portal'],

            // Announcements
            ['code' => 'hcm.announcements.view', 'name' => 'View Announcements', 'module' => 'self_service', 'description' => 'View company and HR announcements'],
            ['code' => 'hcm.announcements.manage', 'name' => 'Manage Announcements', 'module' => 'self_service', 'description' => 'Create and edit announcements and target audiences'],
            ['code' => 'hcm.announcements.publish', 'name' => 'Publish Announcements', 'module' => 'self_service', 'description' => 'Publish announcements with acknowledgement requirements'],

            // Document Certificates
            ['code' => 'hcm.documents.request.view', 'name' => 'View Document Requests', 'module' => 'self_service', 'description' => 'View generated letters and certificate requests'],
            ['code' => 'hcm.documents.request.manage', 'name' => 'Manage Document Templates', 'module' => 'self_service', 'description' => 'Configure HR letter templates and issue certificates'],

            // Reports & Analytics
            ['code' => 'hcm.service_reports.view', 'name' => 'View Service Analytics', 'module' => 'self_service', 'description' => 'View service desk metrics, SLA compliance, and CSAT scores'],
            ['code' => 'hcm.service_reports.export', 'name' => 'Export Service Reports', 'module' => 'self_service', 'description' => 'Export service delivery and agent productivity reports'],
        ];

        foreach ($permissions as $perm) {
            $exists = DB::table('permissions')->where('code', $perm['code'])->first();
            if (! $exists) {
                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => $perm['code'],
                    'name' => $perm['name'],
                    'module' => $perm['module'],
                    'description' => $perm['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
