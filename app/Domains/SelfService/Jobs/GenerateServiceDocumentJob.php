<?php

namespace App\Domains\SelfService\Jobs;

use App\Domains\SelfService\Models\HrServiceRequest;
use App\Domains\SelfService\Models\HrServiceTemplate;
use App\Domains\SelfService\Services\RequestTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateServiceDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public HrServiceRequest $request,
        public HrServiceTemplate $template
    ) {}

    public function handle(RequestTemplateService $templateService): void
    {
        $templateService->generateDocumentFromTemplate($this->request, $this->template);
    }
}
