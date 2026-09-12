<?php

namespace Database\Seeders;

use App\Domains\Shared\Models\Permission;
use App\Domains\Shared\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeDocumentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $group = PermissionGroup::firstOrCreate(
            ['name' => 'employee_documents'],
            ['label' => 'Employee Document & Digital Personnel File Management']
        );

        $permissions = [
            'employee_documents.view' => 'View employee documents and digital personnel files',
            'employee_documents.view_all' => 'View all employee documents across the enterprise',
            'employee_documents.upload' => 'Upload documents to employee personnel files',
            'employee_documents.update' => 'Update document metadata and replacement versions',
            'employee_documents.verify' => 'Verify and approve submitted employee documents',
            'employee_documents.reject' => 'Reject submitted employee documents with reason',
            'employee_documents.request' => 'Create and send document requests to employees',
            'employee_documents.acknowledge' => 'Record employee digital document acknowledgements',
            'employee_documents.download' => 'Securely download authorized employee documents',
            'employee_documents.manage_types' => 'Configure document categories and document types',
            'employee_documents.manage_requirements' => 'Assign, modify, and waive document requirements',
            'employee_documents.manage_retention' => 'Configure document retention and disposal policies',
            'employee_documents.bulk_upload' => 'Perform bulk employee document uploads and imports',
            'employee_documents.view_sensitive' => 'Access confidential and restricted documents',
            'employee_documents.view_er' => 'Access confidential Employee Relations documents',
            'employee_documents.view_medical' => 'Access employee medical and health certificates',
            'employee_documents.view_compensation' => 'Access compensation, salary, and tax documents',
            'employee_documents.view_audit' => 'View historical document audit logs and verifications',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'permission_group_id' => $group->id,
                    'uuid' => (string) Str::uuid(),
                    'label' => ucwords(str_replace(['employee_documents.', '.'], ['', ' '], $name)),
                    'module' => 'employee_documents',
                    'resource' => 'documents',
                    'action' => 'manage',
                    'description' => $description,
                    'status' => 'active',
                ]
            );
        }
    }
}
