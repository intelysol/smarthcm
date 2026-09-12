<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
class ModuleCreateCommand extends Command
{
    protected $signature = 'flow:module:create {name} {--path=app/Domains}';
    protected $description = 'Scaffold a platform domain module.';
    public function handle(): int
    {
        $name = Str::studly($this->argument('name')); abort_if($name === '' || str_contains($name, '..'), 1, 'Invalid module name.');
        $root = base_path(trim($this->option('path'), '/\\').DIRECTORY_SEPARATOR.$name); abort_if(File::exists($root), 1, "Module {$name} already exists.");
        foreach (['Models', 'Services', 'Actions', 'DTOs', 'Requests', 'Resources', 'Http/Controllers', 'Routes', 'Policies', 'Tests'] as $directory) File::ensureDirectoryExists($root.DIRECTORY_SEPARATOR.$directory);
        File::put($root.'/README.md', "# {$name}\n\nGenerated FEP module. Keep tenant boundaries, permissions, tests, and domain ownership explicit.\n");
        File::put($root.'/Routes/api.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::prefix('v1/".Str::kebab($name)."')->group(function (): void {});\n");
        $this->info("Created {$root}"); return self::SUCCESS;
    }
}
