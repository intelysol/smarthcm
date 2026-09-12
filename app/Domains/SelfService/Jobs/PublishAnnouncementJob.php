<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Models\HrAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public HrAnnouncement $announcement) {}

    public function handle(): void
    {
        $this->announcement->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
