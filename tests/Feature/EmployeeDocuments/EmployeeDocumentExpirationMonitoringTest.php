<?php

namespace Tests\Feature\EmployeeDocuments;

use App\Domains\Employee\Models\Employee;
use App\Domains\EmployeeDocuments\Enums\DocumentStatus;
use App\Domains\EmployeeDocuments\Jobs\MonitorEmployeeDocumentExpirationJob;
use App\Domains\EmployeeDocuments\Models\EmployeeDocument;
use App\Domains\EmployeeDocuments\Models\HcmDocumentCategory;
use App\Domains\EmployeeDocuments\Models\HcmDocumentType;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentExpirationService;
use App\Domains\EmployeeDocuments\Services\EmployeeDocumentService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDocumentExpirationMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_expiration_monitoring_milestones_and_suppression(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'employee_code' => 'EMP-EXP-1',
            'employee_number' => 'EMP-EXP-1',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'official_email' => 'dwight@example.com',
            'joining_date' => now()->toDateString(),
            'employment_status' => 'active',
        ]);

        $category = HcmDocumentCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'COMPLIANCE',
            'name' => 'Compliance',
        ]);

        $type = HcmDocumentType::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'code' => 'WORK_PERMIT',
            'name' => 'Work Permit',
            'expires' => true,
        ]);

        $docService = new EmployeeDocumentService();

        // 1. Expired document
        $expiredDoc = $docService->storeDocument($user, $employee, $type, null, [
            'title' => 'Expired Work Permit',
            'document_number' => 'WP-EXP',
            'expiry_date' => now()->subDays(5)->toDateString(),
        ]);

        // 2. Document expiring in 15 days (within 30 days)
        $expiringSoonDoc = $docService->storeDocument($user, $employee, $type, null, [
            'title' => 'Renewable Permit',
            'document_number' => 'WP-SOON',
            'expiry_date' => now()->addDays(15)->toDateString(),
        ]);

        $expService = new EmployeeDocumentExpirationService();

        // Run check first time
        $results1 = $expService->checkExpirations($tenant->id);
        $this->assertEquals(1, $results1['expired']);
        $this->assertEquals(1, $results1['within_30_days']);
        $this->assertEquals(2, $results1['events_created']);
        $this->assertEquals(DocumentStatus::EXPIRED->value, $expiredDoc->fresh()->status);

        // Run check second time (duplicate suppression check: 0 new events created)
        $results2 = $expService->checkExpirations($tenant->id);
        $this->assertEquals(0, $results2['events_created']);

        // Run Job
        $job = new MonitorEmployeeDocumentExpirationJob();
        $jobResults = $job->handle($expService);
        $this->assertArrayHasKey($tenant->id, $jobResults);
    }
}
