<?php

namespace Tests\Feature\SelfService;

use App\Domains\Employee\Models\Employee;
use App\Domains\Organization\Models\Company;
use App\Domains\SelfService\Enums\ServiceCommentType;
use App\Domains\SelfService\Models\HrServiceCategory;
use App\Domains\SelfService\Models\HrServiceDefinition;
use App\Domains\SelfService\Models\HrServiceVersion;
use App\Domains\SelfService\Services\RequestCommunicationService;
use App\Domains\SelfService\Services\ServiceRequestService;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceCommunicationAndInternalNotePrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_note_isolation_and_privacy_enforcement(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        
        $employeeUser = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'employee_code' => 'EMP-400',
            'employee_number' => 'EMP-400',
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'official_email' => 'diana@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $hrUser = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $category = HrServiceCategory::create([
            'tenant_id' => $tenant->id,
            'code' => 'CAT-HR',
            'name' => 'HR Operations',
        ]);

        $service = HrServiceDefinition::create([
            'tenant_id' => $tenant->id,
            'hr_service_category_id' => $category->id,
            'service_code' => 'SVC-GENERAL',
            'name' => 'General Inquiry',
        ]);

        $version = HrServiceVersion::create([
            'tenant_id' => $tenant->id,
            'hr_service_definition_id' => $service->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
        ]);

        $requestService = app(ServiceRequestService::class);
        $commService = app(RequestCommunicationService::class);

        $request = $requestService->createRequest($employee, $service, ['subject' => 'Disciplinary question']);

        // 1. Employee posts public comment
        $publicCmt = $commService->addComment($request, $employeeUser, 'Hello, I need clarification on attendance deductions.');
        $this->assertEquals(ServiceCommentType::PUBLIC->value, $publicCmt->comment_type);

        // 2. HR Agent posts internal note
        $internalNote = $commService->addComment($request, $hrUser, 'Discussed with payroll lead: employee was absent without notice on Friday.', ServiceCommentType::INTERNAL->value);
        $this->assertEquals(ServiceCommentType::INTERNAL->value, $internalNote->comment_type);

        // 3. Employee tries to post internal note (must be rejected)
        $this->expectException(ValidationException::class);
        $commService->addComment($request, $employeeUser, 'Secret note by employee', ServiceCommentType::INTERNAL->value);
    }

    public function test_employee_query_filters_out_internal_notes(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        
        $employeeUser = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $employee = Employee::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'user_id' => $employeeUser->id,
            'employee_code' => 'EMP-401',
            'employee_number' => 'EMP-401',
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'official_email' => 'diana2@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        $hrUser = User::factory()->create(['tenant_id' => $tenant->id]);

        $category = HrServiceCategory::create(['tenant_id' => $tenant->id, 'code' => 'CAT-HR', 'name' => 'HR']);
        $service = HrServiceDefinition::create(['tenant_id' => $tenant->id, 'hr_service_category_id' => $category->id, 'service_code' => 'SVC-GEN', 'name' => 'General']);
        $version = HrServiceVersion::create(['tenant_id' => $tenant->id, 'hr_service_definition_id' => $service->id, 'version_number' => 1, 'effective_from' => '2026-01-01']);

        $requestService = app(ServiceRequestService::class);
        $commService = app(RequestCommunicationService::class);

        $request = $requestService->createRequest($employee, $service, ['subject' => 'General inquiry']);
        $commService->addComment($request, $employeeUser, 'Public message 1');
        $commService->addComment($request, $hrUser, 'Internal Agent Note', ServiceCommentType::INTERNAL->value);
        $commService->addComment($request, $hrUser, 'Public response from HR', ServiceCommentType::PUBLIC->value);

        // Employee sees only 2 public comments
        $empComments = $commService->getVisibleCommentsForUser($request, $employeeUser);
        $this->assertCount(2, $empComments);
        $this->assertFalse($empComments->contains('comment_type', ServiceCommentType::INTERNAL->value));

        // HR agent sees all 3 comments
        $hrComments = $commService->getVisibleCommentsForUser($request, $hrUser);
        $this->assertCount(3, $hrComments);
    }
}
