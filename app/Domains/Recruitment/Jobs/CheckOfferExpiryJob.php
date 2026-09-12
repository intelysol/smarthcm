<?php

namespace App\Domains\Recruitment\Jobs;

use App\Domains\Recruitment\Enums\OfferStatus;
use App\Domains\Recruitment\Models\HcmRecruitmentOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckOfferExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        HcmRecruitmentOffer::where('status', OfferStatus::SENT->value)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->toDateString())
            ->update([
                'status' => OfferStatus::EXPIRED->value,
            ]);
    }
}
