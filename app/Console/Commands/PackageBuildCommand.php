<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
class PackageBuildCommand extends Command
{
    protected $signature = 'flow:package:build {path}';
    protected $description = 'Validate a FEP package manifest.';
    public function handle(): int
    {
        $path = base_path(trim($this->argument('path'), '/\\')); $manifest = $path.'/fep-plugin.json';
        if (! File::exists($manifest)) { $this->error('Missing fep-plugin.json'); return self::FAILURE; }
        $data = json_decode(File::get($manifest), true); if (! is_array($data) || ! isset($data['name'], $data['version'], $data['entrypoint'])) { $this->error('Manifest requires name, version, and entrypoint.'); return self::FAILURE; }
        if (! File::exists($path.'/'.$data['entrypoint'])) { $this->error('Manifest entrypoint does not exist.'); return self::FAILURE; }
        $this->info("Package {$data['name']} v{$data['version']} is valid."); return self::SUCCESS;
    }
}
