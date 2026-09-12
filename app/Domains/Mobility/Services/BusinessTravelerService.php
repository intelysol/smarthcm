<?php

namespace App\Domains\Mobility\Services;

use App\Domains\Mobility\Models\MobilityBusinessTraveler;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BusinessTravelerService
{
    /**
     * Register a business travel trip and calculate compliance / PE risk level.
     */
    public function registerTrip(array $data): MobilityBusinessTraveler
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $tripDays = max(1, $startDate->diffInDays($endDate) + 1);

        $tripNumber = 'TRV-' . strtoupper(Str::random(8));

        // Calculate cumulative days spent in destination country in the rolling 12 months
        $rollingStart = now()->subYear()->toDateString();
        $cumulativeDays = MobilityBusinessTraveler::where('tenant_id', $data['tenant_id'])
            ->where('employee_id', $data['employee_id'])
            ->where('destination_country', $data['destination_country'])
            ->where('start_date', '>=', $rollingStart)
            ->sum('trip_days');

        $totalDaysWithTrip = $cumulativeDays + $tripDays;

        // Risk Level calculation:
        // Over 90 days: High (risk of permanent establishment / tax residency)
        // Over 30 days: Medium
        // Under 30 days: Low
        $riskLevel = 'low';
        if ($totalDaysWithTrip >= 90) {
            $riskLevel = 'high';
        } elseif ($totalDaysWithTrip >= 30) {
            $riskLevel = 'medium';
        }

        return MobilityBusinessTraveler::create([
            'tenant_id' => $data['tenant_id'],
            'employee_id' => $data['employee_id'],
            'traveler_trip_number' => $tripNumber,
            'destination_country' => $data['destination_country'],
            'destination_city' => $data['destination_city'] ?? null,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'trip_days' => $tripDays,
            'business_purpose' => $data['business_purpose'],
            'compliance_risk_level' => $riskLevel,
            'visa_required' => (bool) ($data['visa_required'] ?? false),
            'visa_cleared' => (bool) ($data['visa_cleared'] ?? true),
            'expense_travel_request_id' => $data['expense_travel_request_id'] ?? null,
            'status' => 'registered',
        ]);
    }
}
