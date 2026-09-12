<?php

namespace App\Domains\Learning\Events;

use App\Domains\Learning\Models\LearningCertificate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LearningCertificateExpired
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LearningCertificate $certificate) {}
}
