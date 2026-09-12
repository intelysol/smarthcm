<?php

namespace App\Domains\EmployeeDocuments\Services;

use App\Domains\EmployeeDocuments\Enums\DocumentConfidentiality;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class EmployeeDocumentSecurityService
{
    public function canView(User $user, EmployeeDocument $doc): bool
    {
        // 1. Tenant Isolation
        if ((string) $user->tenant_id !== (string) $doc->tenant_id) {
            return false;
        }

        // 2. Platform Admin
        if ($user->is_platform_admin) {
            return true;
        }

        // 3. Category/Level Special Checks
        $categoryCode = $doc->documentType?->category?->code ?? '';

        // ER documents require explicit ER view permission
        if ($categoryCode === 'EMPLOYEE_RELATIONS' || $doc->confidentiality_level === DocumentConfidentiality::HIGHLY_RESTRICTED->value) {
            return method_exists($user, 'hasPermission') && $user->hasPermission('employee_documents.view_er');
        }

        // Medical documents require medical view permission
        if ($categoryCode === 'COMPLIANCE' && str_contains(strtolower($doc->title), 'medical')) {
            return method_exists($user, 'hasPermission') && $user->hasPermission('employee_documents.view_medical');
        }

        // Compensation/Tax documents require compensation view permission for non-owners
        $isOwner = !empty($user->employee_id) && (string) $user->employee_id === (string) $doc->employee_id;
        if (in_array($categoryCode, ['COMPENSATION', 'TAX']) && !$isOwner) {
            if (!(method_exists($user, 'hasPermission') && $user->hasPermission('employee_documents.view_compensation'))) {
                return false;
            }
        }

        // 4. Employee Self-Service
        if ($isOwner) {
            return $doc->employee_visible;
        }

        // 5. Manager Scope
        $employee = $doc->employee;
        if ($employee && (string) $employee->reporting_manager_id === (string) $user->employee_id) {
            if (!$doc->manager_visible) {
                return false;
            }
            if (in_array($doc->confidentiality_level, [DocumentConfidentiality::HR_CONFIDENTIAL->value, DocumentConfidentiality::RESTRICTED->value])) {
                return false;
            }
            return true;
        }

        // 6. HR general permission
        return method_exists($user, 'hasPermission') && $user->hasPermission('employee_documents.view');
    }

    public function authorizeAccess(User $user, EmployeeDocument $doc, string $action = 'view'): void
    {
        if ((string) $user->tenant_id !== (string) $doc->tenant_id) {
            throw new AuthorizationException('Cross-tenant employee document access prohibited.');
        }

        if (!$this->canView($user, $doc)) {
            throw new AuthorizationException('Access denied: You are not authorized to view or access this employee document.');
        }

        if ($action === 'download') {
            $canDownload = $user->is_platform_admin
                || (!empty($user->employee_id) && (string) $user->employee_id === (string) $doc->employee_id)
                || (method_exists($user, 'hasPermission') && $user->hasPermission('employee_documents.download'));

            if (!$canDownload) {
                throw new AuthorizationException('Access denied: You lack permission to download this document.');
            }
        }
    }

    public function getSecureDownloadUrl(EmployeeDocument $doc): string
    {
        $version = $doc->sharedDocument?->versions()->latest('version')->first();
        $path = $version?->storage_path ?? "documents/{$doc->id}.pdf";

        // Generate HMAC signed temporary download route
        return url("/api/v1/hcm/employee-documents/{$doc->id}/download?signature=" . hash_hmac('sha256', $path, config('app.key')));
    }
}
