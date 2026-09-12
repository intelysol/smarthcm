<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
class ArchitectureTestCommand extends Command
{
    protected $signature = 'flow:test:architecture {--strict}';
    protected $description = 'Validate domain architecture boundaries.';
    public function handle(): int
    {
        $violations = [];
        foreach (File::allFiles(base_path('app/Domains')) as $file) {
            $contents = File::get($file->getPathname());
            if (str_contains($contents, 'request()->header(\'X-Tenant\')')) $violations[] = $file->getRelativePathname().': client tenant header access';
        }
        if ($violations) { $this->error(implode(PHP_EOL, $violations)); return self::FAILURE; }
        $this->info('Architecture checks passed.'); return self::SUCCESS;
    }
}
