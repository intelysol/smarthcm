<?php

namespace App\Domains\Offboarding\Jobs;

use App\Domains\Employee\Models\Employment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DetectContractExpiriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): array
    {
        $upcomingExpiries = Employment::whereNotNull('end_date')
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereDate('end_date', '<=', now()->addDays(90)->toDateString())
            ->with(['employee', 'tenant'])
            ->get();

        Log::info('DetectContractExpiriesJob: Identified upcoming contract expiries.', [
            'count' => $upcomingExpiries->count(),
        ]);

        return $upcomingExpiries->toArray();
    }
}
