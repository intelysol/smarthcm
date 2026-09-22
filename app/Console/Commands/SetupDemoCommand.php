<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DemoEnvironmentSeeder;
use Illuminate\Console\Command;

class SetupDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-demo {--force : Force demo setup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely seed demo organization, roles, permissions, and demo users for local development or evaluation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Initializing Smart HCM / Flow Enterprise Platform Demo Setup...");

        if (app()->environment('production') && !env('SEED_DEMO_USERS', false) && !$this->option('force')) {
            $this->error("CRITICAL SAFETY ERROR: Demo setup cannot be executed in production environments.");
            $this->error("If this is intentional, set SEED_DEMO_USERS=true in your environment or supply --force.");
            return Command::FAILURE;
        }

        try {
            $this->call(DemoEnvironmentSeeder::class);

            $this->newLine();
            $this->info("=== DEMO ENVIRONMENT SEEDED SUCCESSFULLY ===");
            $this->table(
                ['Role', 'Email', 'Scope', 'Default Workspace', 'Target URL'],
                [
                    ['Platform Super Admin', 'superadmin@example.test', 'Global Platform', 'Platform Control Center', '/platform'],
                    ['Tenant Admin', 'admin@example.test', 'Demo Tenant', 'Tenant Administration', '/admin'],
                    ['HR Administrator', 'hr@example.test', 'Demo Tenant', 'HR Command Center', '/hr'],
                    ['People Manager', 'manager@example.test', 'Demo Tenant (Team)', 'Manager Workbench', '/manager'],
                    ['Employee', 'employee@example.test', 'Demo Tenant (Self)', 'Employee Portal', '/portal'],
                ]
            );

            $this->newLine();
            $this->comment("Password: Defined by DEMO_USER_PASSWORD in .env (default: 'Demo1234!@#$')");
            $this->comment("Login Portal: " . url('/login'));
            $this->newLine();

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Demo setup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
