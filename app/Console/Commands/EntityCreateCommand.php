<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
class EntityCreateCommand extends Command
{
    protected $signature = 'flow:entity:create {name} {--domain=Platform}';
    protected $description = 'Generate a metadata-ready entity model, migration, and API placeholders.';
    public function handle(): int
    {
        $name = Str::studly($this->argument('name')); $domain = Str::studly($this->option('domain')); if ($name === '' || $domain === '') return self::FAILURE;
        $root = base_path("app/Domains/{$domain}"); File::ensureDirectoryExists($root.'/Models'); File::ensureDirectoryExists($root.'/Services'); File::ensureDirectoryExists($root.'/Requests');
        $model = "<?php\nnamespace App\\Domains\\{$domain}\\Models;\nuse Illuminate\\Database\\Eloquent\\Model;\nclass {$name} extends Model { protected \\$guarded = []; }\n";
        File::put($root."/Models/{$name}.php", $model); $this->info("Generated {$domain}/{$name}"); return self::SUCCESS;
    }
}
