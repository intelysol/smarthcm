<?php

declare(strict_types=1);

namespace App\Domains\PublicWebsite\Services;

use App\Domains\PublicWebsite\Models\PublicLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicLeadService
{
    /**
     * Store a new lead inquiry from public website forms.
     *
     * @param array<string, mixed> $data
     * @param Request $request
     * @return PublicLead
     */
    public function captureLead(array $data, Request $request): PublicLead
    {
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = substr((string) $request->userAgent(), 0, 500);
        $data['status'] = 'new';

        $lead = PublicLead::create($data);

        Log::info('Public website lead inquiry captured', [
            'lead_id' => $lead->id,
            'type' => $lead->type,
            'company' => $lead->company,
            'work_email' => $lead->work_email,
        ]);

        return $lead;
    }
}
