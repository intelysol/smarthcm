<?php

declare(strict_types=1);

namespace Tests\Feature\PublicWebsite;

use App\Domains\PublicWebsite\Models\PublicLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteLeadGenerationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful demo request submission.
     */
    public function test_demo_request_form_submission_success(): void
    {
        $payload = [
            'name' => 'Sarah Connor',
            'company' => 'Cyberdyne Systems Corp',
            'work_email' => 'sarah@cyberdyne.example',
            'phone' => '+1-555-0199',
            'country' => 'United States',
            'organization_size' => '251-1000',
            'hcm_requirements' => 'Mobile GPS Attendance and Multi-Country Payroll',
            'message' => 'Looking to replace legacy clock terminals across 5 branches.',
        ];

        $response = $this->post('/demo', $payload);

        $response->assertRedirect(route('public.demo'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('public_leads', [
            'type' => 'demo',
            'company' => 'Cyberdyne Systems Corp',
            'work_email' => 'sarah@cyberdyne.example',
            'organization_size' => '251-1000',
        ]);
    }

    /**
     * Test demo form validation error on missing required fields.
     */
    public function test_demo_request_validation_failure(): void
    {
        $response = $this->post('/demo', [
            'name' => 'John Doe',
            // Missing company, work_email, phone, organization_size
        ]);

        $response->assertSessionHasErrors(['company', 'work_email', 'phone', 'organization_size']);
        $this->assertDatabaseCount('public_leads', 0);
    }

    /**
     * Test contact inquiry form submission.
     */
    public function test_contact_form_submission_success(): void
    {
        $payload = [
            'name' => 'Michael Scott',
            'company' => 'Dunder Mifflin Paper Co',
            'work_email' => 'michael@dundermifflin.example',
            'phone' => '+1-555-0144',
            'message' => 'We need information on vacation accrual tracking.',
        ];

        $response = $this->post('/contact', $payload);

        $response->assertRedirect(route('public.contact'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('public_leads', [
            'type' => 'contact',
            'company' => 'Dunder Mifflin Paper Co',
            'work_email' => 'michael@dundermifflin.example',
        ]);
    }
}
