<?php

namespace Tests\Feature\Expenses;

use App\Domains\Employee\Models\Employee;
use App\Domains\Expenses\Enums\TravelStatus;
use App\Domains\Expenses\Enums\TravelType;
use App\Domains\Expenses\Models\TravelRequest;
use App\Domains\Expenses\Services\TravelAuthorizationService;
use App\Domains\Expenses\Services\TravelRequestService;
use App\Domains\Organization\Models\Company;
use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_request_multi_segment_itinerary_and_authorization(): void
    {
        $tenant = Tenant::factory()->create();
        $company = Company::factory()->create(['tenant_id' => $tenant->id]);
        $employee = $this->createEmployee($tenant->id, $company->id);
        $approver = User::factory()->create(['tenant_id' => $tenant->id]);

        $travelService = app(TravelRequestService::class);

        // 1. Create Travel Request with multi-segment itinerary
        $travel = $travelService->createTravelRequest($employee, [
            'travel_type' => TravelType::INTERNATIONAL->value,
            'destination' => 'London, UK',
            'purpose' => 'Annual International Tech Conference & Client Meetings',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-10',
            'estimated_cost' => 4500.0000,
            'currency' => 'USD',
            'business_justification' => 'Keynote speaker and VIP enterprise client meetings.',
            'segments' => [
                [
                    'origin' => 'Karachi (KHI)',
                    'destination' => 'Dubai (DXB)',
                    'departure_time' => '2026-10-01 04:00:00',
                    'transport_type' => 'flight',
                    'carrier_name' => 'Emirates',
                    'booking_reference' => 'EK-902',
                ],
                [
                    'origin' => 'Dubai (DXB)',
                    'destination' => 'London Heathrow (LHR)',
                    'departure_time' => '2026-10-01 10:00:00',
                    'transport_type' => 'flight',
                    'carrier_name' => 'Emirates',
                    'booking_reference' => 'EK-001',
                ],
            ],
        ]);

        $this->assertEquals(TravelStatus::DRAFT->value, $travel->status);
        $this->assertEquals(2, $travel->segments()->count());
        $this->assertEquals('London, UK', $travel->destination);

        // 2. Add Hotel Booking
        $booking = $travelService->addBooking($travel, [
            'booking_type' => 'hotel',
            'provider_name' => 'Hilton London Metropole',
            'booking_reference' => 'HLT-9921',
            'start_date' => '2026-10-01 14:00:00',
            'end_date' => '2026-10-10 11:00:00',
            'cost' => 2000.0000,
            'currency' => 'USD',
        ]);

        $this->assertEquals(1, $travel->bookings()->count());

        // 3. Submit Travel Request
        $submitted = $travelService->submitTravelRequest($travel);
        $this->assertEquals(TravelStatus::SUBMITTED->value, $submitted->status);

        // 4. Approve and Generate Travel Authorization
        $approved = $travelService->approveTravelRequest($submitted, $approver, 4500.0000);
        $this->assertEquals(TravelStatus::APPROVED->value, $approved->status);
        $this->assertNotNull($approved->authorization);
        $this->assertStringStartsWith('TA-2026-', $approved->authorization->authorization_number);
        $this->assertEquals(4500.0000, (float) $approved->authorization->approved_budget);
    }

    protected function createEmployee(string $tenantId, string $companyId, array $attributes = []): Employee
    {
        return Employee::create(array_merge([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'employee_code' => 'EMP-' . uniqid(),
            'employee_number' => 'EMP-' . rand(1000, 9999),
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
            'official_email' => 'alex.' . uniqid() . '@example.com',
            'employment_status' => 'active',
            'joining_date' => '2026-01-01',
        ], $attributes));
    }
}
