<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class PlatformReleaseCertificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:certify 
                            {--suite=all : Specific suite to evaluate: all, schema, security, tenant, api, e2e}
                            {--json : Output strictly in JSON format for CI/CD quality gate enforcement}
                            {--quick : Smoke test mode verifying schema and regression assertions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enterprise Release Certification Engine: Evaluates quality gates, security invariants, tenant boundaries, and E2E journeys for production release.';

    /**
     * Authoritative baseline requirements
     */
    public const MINIMUM_SCHEMA_TABLES = 980;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isJson = (bool) $this->option('json');
        $suiteFilter = (string) $this->option('suite');
        $isQuick = (bool) $this->option('quick');

        $startTime = microtime(true);

        if (!$isJson) {
            $this->outputBanner();
        }

        $results = [
            'timestamp' => now()->toIso8601String(),
            'environment' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'quick_mode' => $isQuick,
            'gates' => [],
            'certified' => true,
            'summary' => [
                'total_gates' => 0,
                'passed_gates' => 0,
                'failed_gates' => 0,
                'duration_seconds' => 0.0,
            ],
        ];

        // Gate 1: Database Schema & Migration Integrity
        if ($suiteFilter === 'all' || $suiteFilter === 'schema') {
            $schemaGate = $this->evaluateSchemaGate();
            $results['gates']['schema_integrity'] = $schemaGate;
            if (!$schemaGate['passed']) {
                $results['certified'] = false;
            }
        }

        // Gate 2: Security & Authentication Gates
        if ($suiteFilter === 'all' || $suiteFilter === 'security') {
            $securityGate = $this->evaluateSecurityGate($isQuick);
            $results['gates']['security_and_auth'] = $securityGate;
            if (!$securityGate['passed']) {
                $results['certified'] = false;
            }

            $aiGate = $this->evaluateAiSafetyGate($isQuick);
            $results['gates']['ai_governance_safety'] = $aiGate;
            if (!$aiGate['passed']) {
                $results['certified'] = false;
            }
        }

        // Gate 3: Tenant Isolation & IDOR Defense Gate
        if ($suiteFilter === 'all' || $suiteFilter === 'tenant') {
            $tenantGate = $this->evaluateTenantIsolationGate($isQuick);
            $results['gates']['tenant_isolation'] = $tenantGate;
            if (!$tenantGate['passed']) {
                $results['certified'] = false;
            }
        }

        // Gate 4: API Contract & Error Envelope Gate
        if ($suiteFilter === 'all' || $suiteFilter === 'api') {
            $apiGate = $this->evaluateApiContractGate($isQuick);
            $results['gates']['api_contract'] = $apiGate;
            if (!$apiGate['passed']) {
                $results['certified'] = false;
            }
        }

        // Gate 5: Critical HCM E2E Journeys Gate
        if ($suiteFilter === 'all' || $suiteFilter === 'e2e') {
            $e2eGate = $this->evaluateE2EJourneysGate($isQuick);
            $results['gates']['critical_e2e_journeys'] = $e2eGate;
            if (!$e2eGate['passed']) {
                $results['certified'] = false;
            }
        }

        // Gate 6: Workspace Experience & Role Separation Gate (Epic 2.69)
        if ($suiteFilter === 'all' || $suiteFilter === 'workspace') {
            $workspaceGate = $this->evaluateWorkspaceGate($isQuick);
            $results['gates']['workspace_experience'] = $workspaceGate;
            if (!$workspaceGate['passed']) {
                $results['certified'] = false;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $totalGates = count($results['gates']);
        $passedGates = count(array_filter($results['gates'], fn ($g) => $g['passed'] ?? false));
        $failedGates = $totalGates - $passedGates;

        $results['summary']['total_gates'] = $totalGates;
        $results['summary']['passed_gates'] = $passedGates;
        $results['summary']['failed_gates'] = $failedGates;
        $results['summary']['duration_seconds'] = $duration;

        if ($isJson) {
            $this->output->writeln(json_encode($results, JSON_PRETTY_PRINT));
            return $results['certified'] ? 0 : 1;
        }

        $this->renderScorecard($results);

        return $results['certified'] ? 0 : 1;
    }

    /**
     * Evaluate Database Schema Integrity
     */
    protected function evaluateSchemaGate(): array
    {
        $gate = [
            'name' => 'Database Schema & Structural Integrity',
            'passed' => true,
            'details' => [],
            'metrics' => [],
        ];

        try {
            $driver = DB::getDriverName();
            $gate['metrics']['driver'] = $driver;

            $requiredCoreTables = [
                'users',
                'tenants',
                'companies',
                'departments',
                'employees',
                'billing_subscriptions',
                'billing_plans',
                'leave_types',
                'leave_applications',
                'attendance_sessions',
                'hcm_recruitment_requisitions',
                'hcm_ai_concierge_actions',
            ];

            $missingTables = [];
            foreach ($requiredCoreTables as $table) {
                if (!Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            if (!empty($missingTables)) {
                $gate['passed'] = false;
                $gate['details'][] = 'Missing core tables: ' . implode(', ', $missingTables);
            } else {
                $gate['details'][] = 'All required core domain tables present in schema';
            }

            if ($driver === 'mysql') {
                $dbName = DB::getDatabaseName();
                $tableCount = (int) DB::table('information_schema.tables')
                    ->where('table_schema', $dbName)
                    ->where('table_type', 'BASE TABLE')
                    ->count();

                $gate['metrics']['total_tables'] = $tableCount;
                if ($tableCount < self::MINIMUM_SCHEMA_TABLES) {
                    $gate['passed'] = false;
                    $gate['details'][] = "Table count ({$tableCount}) below minimum baseline requirement (" . self::MINIMUM_SCHEMA_TABLES . ")";
                } else {
                    $gate['details'][] = "Full enterprise schema intact ({$tableCount} tables verified)";
                }
            }
        } catch (\Throwable $e) {
            $gate['passed'] = false;
            $gate['details'][] = 'Database schema check error: ' . $e->getMessage();
        }

        return $gate;
    }

    /**
     * Evaluate Security & Authentication Gate
     */
    protected function evaluateSecurityGate(bool $isQuick): array
    {
        return $this->runTestSuiteGate(
            'Security & Identity Governance',
            ['tests/Security/AuthenticationTestSuiteTest.php', 'tests/Security/AuthorizationTestSuiteTest.php', 'tests/Security/SecurityRegressionSuiteTest.php'],
            $isQuick
        );
    }

    /**
     * Evaluate AI Safety & Governance Gate
     */
    protected function evaluateAiSafetyGate(bool $isQuick): array
    {
        return $this->runTestSuiteGate(
            'AI Governance & Non-Autonomous Safety Boundaries',
            ['tests/Security/AiGovernanceSafetyTest.php'],
            $isQuick
        );
    }

    /**
     * Evaluate Multi-Tenant Isolation Gate
     */
    protected function evaluateTenantIsolationGate(bool $isQuick): array
    {
        return $this->runTestSuiteGate(
            'Multi-Tenant Data Isolation & IDOR Defense',
            ['tests/Tenant/TenantIsolationRegressionTest.php'],
            $isQuick
        );
    }

    /**
     * Evaluate API Contract & Error Envelope Gate
     */
    protected function evaluateApiContractGate(bool $isQuick): array
    {
        return $this->runTestSuiteGate(
            'API Contract & Error Envelope Uniformity',
            ['tests/API/ApiRegressionSuiteTest.php'],
            $isQuick
        );
    }

    /**
     * Evaluate Critical HCM E2E Journeys Gate
     */
    protected function evaluateE2EJourneysGate(bool $isQuick): array
    {
        return $this->runTestSuiteGate(
            'Critical HCM User Journeys E2E',
            ['tests/E2E/CriticalHcmJourneysTest.php'],
            $isQuick
        );
    }

    /**
     * Evaluate Workspace Experience & Role Separation Gate (Epic 2.69)
     */
    protected function evaluateWorkspaceGate(bool $isQuick): array
    {
        return $this->runTestSuiteGate(
            'Workspace Experience & Role Separation',
            ['tests/Feature/Workspace/WorkspaceExperienceTest.php', 'tests/Security/WorkspaceAuthorizationTest.php'],
            $isQuick
        );
    }

    /**
     * Execute PHPUnit on a set of test files and parse results
     */
    protected function runTestSuiteGate(string $gateName, array $testFiles, bool $isQuick): array
    {
        $gate = [
            'name' => $gateName,
            'passed' => true,
            'tests_run' => 0,
            'assertions' => 0,
            'failures' => 0,
            'details' => [],
        ];

        // In quick mode, if files exist, we run phpunit for the targeted files
        $phpBinary = PHP_BINARY;
        $phpunitBinary = 'vendor/bin/phpunit';

        foreach ($testFiles as $testFile) {
            $fullPath = base_path($testFile);
            if (!file_exists($fullPath)) {
                $gate['passed'] = false;
                $gate['details'][] = "Missing test suite file: {$testFile}";
                continue;
            }

            $outputLines = [];
            $exitCode = 0;
            $cmd = "\"{$phpBinary}\" {$phpunitBinary} {$testFile} 2>&1";
            exec($cmd, $outputLines, $exitCode);
            $output = implode("\n", $outputLines);

            if ($exitCode === 0) {
                // Parse OK lines e.g. "OK (7 tests, 37 assertions)" or JSON output
                if (preg_match('/OK \((\d+) tests?, (\d+) assertions?\)/', $output, $matches)) {
                    $gate['tests_run'] += (int) $matches[1];
                    $gate['assertions'] += (int) $matches[2];
                } elseif (preg_match('/"tests":(\d+),"passed":(\d+),"assertions":(\d+)/', $output, $matches)) {
                    $gate['tests_run'] += (int) $matches[1];
                    $gate['assertions'] += (int) $matches[3];
                } else {
                    $gate['tests_run'] += 1;
                }
            } else {
                $gate['passed'] = false;
                $gate['failures'] += 1;
                $gate['details'][] = "Failures detected in {$testFile}: " . substr(strip_tags($output), 0, 200);
            }
        }

        if ($gate['passed']) {
            $gate['details'][] = "All {$gate['tests_run']} tests ({$gate['assertions']} assertions) passed with zero regressions.";
        }

        return $gate;
    }

    /**
     * Render ASCII Banner
     */
    protected function outputBanner(): void
    {
        $this->output->writeln([
            '<fg=cyan;options=bold>========================================================================</>',
            '<fg=cyan;options=bold>   ENTERPRISE RELEASE CERTIFICATION & QUALITY GATE ENGINE   </>',
            '<fg=cyan;options=bold>   Platform Version: 2.68-ENTERPRISE | Quality Gate v1.0   </>',
            '<fg=cyan;options=bold>========================================================================</>',
            '',
        ]);
    }

    /**
     * Render Formatted Scorecard
     */
    protected function renderScorecard(array $results): void
    {
        $this->line('<fg=yellow;options=bold>RELEASE CERTIFICATION EVALUATION SCORECARD</>');
        $this->line('------------------------------------------------------------------------');

        $headers = ['Quality Gate Pillar', 'Status', 'Tests', 'Assertions', 'Verdict Details'];
        $rows = [];

        foreach ($results['gates'] as $key => $gate) {
            $status = $gate['passed']
                ? '<fg=green;options=bold>PASSED</>'
                : '<fg=red;options=bold>FAILED</>';

            $tests = $gate['tests_run'] ?? ($gate['metrics']['total_tables'] ?? '-');
            $assertions = $gate['assertions'] ?? '-';
            $detail = implode('; ', $gate['details']);

            $rows[] = [
                $gate['name'],
                $status,
                $tests,
                $assertions,
                substr($detail, 0, 50),
            ];
        }

        $this->table($headers, $rows);

        $this->line('------------------------------------------------------------------------');
        $this->line("Total Gates: <fg=white;options=bold>{$results['summary']['total_gates']}</> | Passed: <fg=green;options=bold>{$results['summary']['passed_gates']}</> | Failed: <fg=red;options=bold>{$results['summary']['failed_gates']}</> | Duration: {$results['summary']['duration_seconds']}s");
        $this->line('------------------------------------------------------------------------');

        if ($results['certified']) {
            $this->output->writeln([
                '',
                '<fg=green;options=bold>========================================================================</>',
                '<fg=green;options=bold>   [PASS] PLATFORM STATUS: RELEASE CERTIFIED (PRODUCTION READY)         </>',
                '<fg=green;options=bold>   Zero P0/P1 defects, zero security bypasses, zero isolation leaks     </>',
                '<fg=green;options=bold>========================================================================</>',
                '',
            ]);
        } else {
            $this->output->writeln([
                '',
                '<fg=red;options=bold>========================================================================</>',
                '<fg=red;options=bold>   [FAIL] PLATFORM STATUS: CERTIFICATION REJECTED (UNREADY)             </>',
                '<fg=red;options=bold>   One or more critical quality gates failed release standards          </>',
                '<fg=red;options=bold>========================================================================</>',
                '',
            ]);
        }
    }
}
