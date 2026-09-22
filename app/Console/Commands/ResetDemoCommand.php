<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Shared\Models\Tenant;
use App\Models\User;
use Database\Seeders\DemoEnvironmentSeeder;
use Illuminate\Console\Command;

class ResetDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-demo {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely reset and re-seed the demo organization and demo user accounts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error("CRITICAL SAFETY BLOCK: 'app:reset-demo' is strictly prohibited in production.");
            return Command::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Are you sure you want to reset the demo environment and demo users?')) {
            $this->info("Reset aborted.");
            return Command::SUCCESS;
        }

        $this->warn("Purging existing demo accounts...");

        $demoEmails = [
            'superadmin@example.test',
            'admin@example.test',
            'hr@example.test',
            'manager@example.test',
            'employee@example.test',
        ];

        User::query()->whereIn('email', $demoEmails)->forceDelete();

        $this->info("Re-running demo environment seeder...");
        $this->call(DemoEnvironmentSeeder::class);

        $this->info("Demo environment successfully reset.");
        return Command::SUCCESS;
    }
}
