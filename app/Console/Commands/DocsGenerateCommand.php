<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
class DocsGenerateCommand extends Command
{
    protected $signature = 'flow:docs:generate {--output=docs/generated}';
    protected $description = 'Generate an inventory of platform domains and APIs.';
    public function handle(): int
    {
        $output = base_path(trim($this->option('output'), '/\\')); File::ensureDirectoryExists($output); $rows = [];
        foreach (File::directories(base_path('app/Domains')) as $domain) { $name = basename($domain); $rows[] = "- **{$name}** — ".count(File::allFiles($domain)).' source files'; }
        File::put($output.'/platform-inventory.md', "# Flow Enterprise Platform inventory\n\nGenerated: ".now()->toIso8601String()."\n\n".implode(PHP_EOL, $rows).PHP_EOL); $this->info("Generated {$output}/platform-inventory.md"); return self::SUCCESS;
    }
}
