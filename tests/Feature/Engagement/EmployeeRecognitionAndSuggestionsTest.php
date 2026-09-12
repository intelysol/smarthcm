<?php

namespace Tests\Feature\Engagement;

use App\Domains\Employee\Models\Employee;
use App\Domains\Engagement\Models\EmployeeSuggestion;
use App\Domains\Engagement\Models\EngagementRecognition;
use App\Domains\Engagement\Services\EmployeeSuggestionService;
use App\Domains\Engagement\Services\EngagementRecognitionService;
use App\Domains\Organization\Models\Company;
use App\Domains\Platform\Contracts\TenantContext;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\EngagementPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeRecognitionAndSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EngagementPermissionSeeder::class);
    }

    public function test_peer_recognition_lifecycle(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $userA = User::factory()->create(['tenant_id' => $tenant->id]);
        $userB = User::factory()->create(['tenant_id' => $tenant->id]);

        $empA = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userA->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-REC-01',
            'employee_code' => 'EMP-REC-01',
            'first_name' => 'Fatima',
            'last_name' => 'Noor',
            'joining_date' => now()->toDateString(),
        ]);

        $empB = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userB->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-REC-02',
            'employee_code' => 'EMP-REC-02',
            'first_name' => 'Bilal',
            'last_name' => 'Ahmed',
            'joining_date' => now()->toDateString(),
        ]);

        $recService = app(EngagementRecognitionService::class);

        // 1. Create Recognition
        $rec = $recService->createRecognition($tenant->id, $empA, $empB, [
            'recognition_type' => 'peer',
            'value_tag' => 'Customer Obsession',
            'title' => 'Exceptional Client Onboarding',
            'message' => 'Bilal went above and beyond to resolve critical client issues!',
            'visibility' => 'organization',
        ]);

        $this->assertEquals('published', $rec->status);
        $this->assertEquals('Customer Obsession', $rec->value_tag);
        $this->assertEquals(0, $rec->likes_count);

        // 2. Like Recognition
        $likes = $recService->likeRecognition($rec);
        $this->assertEquals(1, $likes);

        // 3. Moderation (e.g. unpublish/archive)
        $moderated = $recService->moderateRecognition($rec, 'archived', $empA);
        $this->assertEquals('archived', $moderated->status);
    }

    public function test_suggestion_box_with_anonymous_submission_and_voting(): void
    {
        $tenant = Tenant::factory()->create();
        app(TenantContext::class)->set($tenant);
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $emp = Employee::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'employee_number' => 'EMP-SUG-01',
            'employee_code' => 'EMP-SUG-01',
            'first_name' => 'Zain',
            'last_name' => 'Ali',
            'joining_date' => now()->toDateString(),
        ]);

        $sugService = app(EmployeeSuggestionService::class);

        // 1. Submit Anonymous Suggestion
        $suggestion = $sugService->submitSuggestion($tenant->id, [
            'category' => 'Wellbeing',
            'title' => 'Ergonomic Desk Accessories Program',
            'description' => 'Subsidize sit-stand converters for remote teammates.',
            'is_anonymous' => true,
        ], $emp);

        $this->assertTrue($suggestion->is_anonymous);
        $this->assertNull($suggestion->employee_id);
        $this->assertEquals(0, $suggestion->votes_count);

        // 2. Vote for Suggestion
        $sugService->voteSuggestion($suggestion, $emp);
        $this->assertEquals(1, $suggestion->fresh()->votes_count);

        // 3. Duplicate voting prevention: second vote by same employee does not increment count
        $sugService->voteSuggestion($suggestion, $emp);
        $this->assertEquals(1, $suggestion->fresh()->votes_count);

        // 4. Review & Implement Suggestion
        $reviewed = $sugService->reviewSuggestion($suggestion, 'accepted', 'Approved by HR Budget Committee', $emp);
        $this->assertEquals('accepted', $reviewed->status);

        $implemented = $sugService->reviewSuggestion($reviewed, 'implemented', 'Vendor agreement finalized', $emp);
        $this->assertEquals('implemented', $implemented->status);
    }
}
