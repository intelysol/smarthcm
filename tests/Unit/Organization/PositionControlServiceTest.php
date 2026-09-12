<?php

namespace Tests\Unit\Organization;

use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Job;
use App\Domains\Organization\Models\Position;
use App\Domains\Organization\Services\PositionControlService;
use App\Domains\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionControlServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_cannot_exceed_approved_headcount_without_override(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $job = Job::query()->create(['tenant_id' => $tenant->id, 'job_code' => 'ENG-1', 'title' => 'Engineer']);
        $position = Position::query()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'job_id' => $job->id, 'code' => 'ENG-001', 'position_code' => 'ENG-001', 'title' => 'Engineer', 'headcount' => 1]);
        $service = app(PositionControlService::class);

        $service->fill($position);
        $this->assertSame(0, $position->refresh()->vacancies());
        $this->expectException(\DomainException::class);
        $service->fill($position);
    }
}
