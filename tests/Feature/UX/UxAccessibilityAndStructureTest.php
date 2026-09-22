<?php

declare(strict_types=1);

namespace Tests\Feature\UX;

use App\Domains\Shared\Enums\WorkspaceType;
use App\Domains\Shared\Models\Tenant;
use App\Domains\Shared\Services\FormattingService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tests\TestCase;

class UxAccessibilityAndStructureTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Acme Corporation',
            'slug' => 'acme-corp',
            'tenant_code' => 'ACM-CORP',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Alice UX Engineer',
            'email' => 'alice@acme.internal',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);
    }

    /**
     * Test all 7 application shells have accessible semantic landmarks and skip links
     */
    public function test_all_workspace_shells_contain_semantic_landmarks_and_skip_links(): void
    {
        $this->actingAs($this->user);

        $shells = [
            'shells.employee',
            'shells.manager',
            'shells.hr',
            'shells.tenant',
            'shells.platform',
            'shells.executive',
            'shells.operations',
        ];

        foreach ($shells as $shell) {
            $rendered = View::make($shell, [
                'navigation' => [],
                'allowedWorkspaces' => [WorkspaceType::EMPLOYEE],
            ])->render();

            // 1. Must have accessible skip link
            $this->assertStringContainsString('skip-link', $rendered, "Shell {$shell} missing skip-link class");
            $this->assertStringContainsString('#main-content', $rendered, "Shell {$shell} missing skip-link href");

            // 2. Must contain semantic HTML5 landmark tags
            $this->assertStringContainsString('<header', $rendered, "Shell {$shell} missing <header> landmark");
            $this->assertStringContainsString('<nav', $rendered, "Shell {$shell} missing <nav> landmark");
            $this->assertStringContainsString('<main id="main-content"', $rendered, "Shell {$shell} missing <main id=\"main-content\">");
            $this->assertStringContainsString('<footer', $rendered, "Shell {$shell} missing <footer> landmark");

            // 3. Must contain command palette & notifications drawer
            $this->assertStringContainsString('open-command-palette', $rendered, "Shell {$shell} missing command palette hook");
            $this->assertStringContainsString('Notification Center', $rendered, "Shell {$shell} missing notification drawer");
        }
    }

    /**
     * Test mobile navigation bottom bar is present in employee and manager shells
     */
    public function test_mobile_bottom_nav_is_rendered_for_employee_and_manager(): void
    {
        $this->actingAs($this->user);

        $employeeHtml = View::make('shells.employee', [
            'navigation' => [],
            'allowedWorkspaces' => [WorkspaceType::EMPLOYEE],
        ])->render();

        $this->assertStringContainsString('aria-label="Mobile Navigation"', $employeeHtml);
        $this->assertStringContainsString('Requests', $employeeHtml);
        $this->assertStringContainsString('Profile', $employeeHtml);

        $managerHtml = View::make('shells.manager', [
            'navigation' => [],
            'allowedWorkspaces' => [WorkspaceType::MANAGER],
        ])->render();

        $this->assertStringContainsString('aria-label="Mobile Navigation"', $managerHtml);
        $this->assertStringContainsString('Workbench', $managerHtml);
        $this->assertStringContainsString('Team', $managerHtml);
    }

    /**
     * Test 404 page renders cleanly without errors and includes workspace-aware button
     */
    public function test_404_page_renders_with_workspace_return_button(): void
    {
        $this->actingAs($this->user);

        $html = View::make('errors.404')->render();

        $this->assertStringContainsString('Page Not Found', $html);
        $this->assertStringContainsString('Error 404', $html);
        $this->assertStringContainsString('Go to', $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
        $this->assertStringNotContainsString('Stack trace', $html);
    }

    /**
     * Test 500 page displays unique reference ID and suppresses sensitive system details
     */
    public function test_500_page_renders_cleanly_with_incident_reference(): void
    {
        $html = View::make('errors.500')->render();

        $this->assertStringContainsString('System Encountered an Error', $html);
        $this->assertStringContainsString('ERR-', $html);
        $this->assertStringContainsString('Contact Support', $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
        $this->assertStringNotContainsString('c:\\laragon\\www', $html);
        $this->assertStringNotContainsString('DB_PASSWORD', $html);
    }

    /**
     * Test centralized FormattingService and Blade directives
     */
    public function test_formatting_service_formats_dates_and_currencies_cleanly(): void
    {
        $service = app(FormattingService::class);

        // Date formatting
        $formattedDate = $service->formatDate('2026-09-16 12:00:00');
        $this->assertNotEmpty($formattedDate);
        $this->assertNotEquals('—', $formattedDate);

        // Currency formatting
        $formattedCurr = $service->formatCurrency(125000.50, '$');
        $this->assertEquals('$ 125,000.50', $formattedCurr);

        // Percentage formatting
        $formattedPct = $service->formatPercentage(98.45);
        $this->assertEquals('98.5%', $formattedPct);

        // Duration formatting
        $formattedDur = $service->formatDuration(150);
        $this->assertEquals('2h 30m', $formattedDur);

        // Directives compilation
        $bladeDate = Blade::compileString("@formatCurrency(500, '$')");
        $this->assertStringContainsString('formatCurrency', $bladeDate);
    }
}
