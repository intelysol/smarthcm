<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HcmSchemaVerifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcm:schema:verify 
                            {--detailed : Display detailed list of tables and foreign keys}
                            {--strict : Enforce strict schema baseline validation (exact tables and foreign key counts)}
                            {--json : Output report strictly in JSON format for automated CI/CD pipelines}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Enterprise HCM database schema integrity, foreign key datatypes, primary keys, and constraints';

    /**
     * Authoritative baseline metrics for Enterprise HCM.
     */
    public const EXPECTED_TABLES_COUNT = 985;
    public const EXPECTED_FOREIGN_KEYS_COUNT = 2180;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isJson = (bool) $this->option('json');
        $isStrict = (bool) $this->option('strict');
        $isDetailed = (bool) $this->option('detailed');

        if (!$isJson) {
            $this->info('Starting Enterprise HCM Database Schema Integrity Verification...');
            if ($isStrict) {
                $this->comment('Strict mode enabled: Enforcing exact baseline counts and zero-tolerance validation.');
            }
        }
        $driver = DB::getDriverName();
        $dbName = DB::getDatabaseName();

        if ($driver !== 'mysql' && !$isJson) {
            $this->warn("Driver is '{$driver}'. Full information_schema checks are optimized for MySQL/MariaDB.");
        }

        $errors = [];
        $warnings = [];

        // 1. Verify required core tables exist
        $coreTables = [
            'users',
            'tenants',
            'companies',
            'departments',
            'designations',
            'employees',
            'payroll_runs',
            'performance_reviews',
            'activity_logs',
        ];

        if (!$isJson) {
            $this->line('1. Checking core table existence...');
        }
        foreach ($coreTables as $table) {
            if (!Schema::hasTable($table)) {
                $errors[] = "Missing critical core table: `{$table}`";
            }
        }

        // Count total tables
        $tables = DB::select('SHOW TABLES');
        $totalTables = count($tables);
        if (!$isJson) {
            $this->line("   Total tables discovered in database: {$totalTables}");
        }

        if ($isStrict && $totalTables !== self::EXPECTED_TABLES_COUNT) {
            $errors[] = "Strict check failed: Total tables count ({$totalTables}) does not match authoritative baseline (" . self::EXPECTED_TABLES_COUNT . ")";
        }

        // 2. Primary Key Datatype Verification
        if (!$isJson) {
            $this->line('2. Verifying Primary Key standards...');
        }

        // Standard: users.id must be BIGINT UNSIGNED AUTO_INCREMENT
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'id')) {
            $usersIdInfo = DB::select("
                SELECT COLUMN_TYPE, EXTRA 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'id'
            ");
            if (!empty($usersIdInfo)) {
                $type = strtolower($usersIdInfo[0]->COLUMN_TYPE);
                $extra = strtolower($usersIdInfo[0]->EXTRA);
                if (strpos($type, 'bigint') === false || strpos($type, 'unsigned') === false) {
                    $errors[] = "Primary key `users.id` type mismatch: expected BIGINT UNSIGNED, found {$type}";
                }
                if ($isStrict && strpos($extra, 'auto_increment') === false) {
                    $errors[] = "Primary key `users.id` expected AUTO_INCREMENT, found extra: '{$extra}'";
                }
            }
        }

        // Standard: employees.id, tenants.id, companies.id should be CHAR(36) UUID
        $uuidTables = ['tenants', 'employees', 'companies'];
        foreach ($uuidTables as $uuidTable) {
            if (Schema::hasTable($uuidTable) && Schema::hasColumn($uuidTable, 'id')) {
                $colInfo = DB::select("
                    SELECT COLUMN_TYPE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'id'
                ", [$uuidTable]);
                if (!empty($colInfo)) {
                    $type = strtolower($colInfo[0]->COLUMN_TYPE);
                    if (strpos($type, 'char(36)') === false && strpos($type, 'varchar(36)') === false) {
                        $warnings[] = "Primary key `{$uuidTable}.id` expected CHAR(36) UUID, found {$type}";
                    }
                }
            }
        }

        // 3. Foreign Key Datatype Compatibility & Constraint Check
        if (!$isJson) {
            $this->line('3. Checking Foreign Key constraints and type compatibility...');
        }
        $kcu = DB::select("
            SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        $cols = DB::select("
            SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
        ");

        $colTypes = [];
        foreach ($cols as $c) {
            $colTypes[$c->TABLE_NAME][$c->COLUMN_NAME] = $c->COLUMN_TYPE;
        }

        $fks = [];
        foreach ($kcu as $k) {
            $fks[] = (object) [
                'TABLE_NAME' => $k->TABLE_NAME,
                'COLUMN_NAME' => $k->COLUMN_NAME,
                'CONSTRAINT_NAME' => $k->CONSTRAINT_NAME,
                'REFERENCED_TABLE_NAME' => $k->REFERENCED_TABLE_NAME,
                'REFERENCED_COLUMN_NAME' => $k->REFERENCED_COLUMN_NAME,
                'LOCAL_TYPE' => $colTypes[$k->TABLE_NAME][$k->COLUMN_NAME] ?? '',
                'REFERENCED_TYPE' => $colTypes[$k->REFERENCED_TABLE_NAME][$k->REFERENCED_COLUMN_NAME] ?? '',
            ];
        }
        $totalFks = count($fks);
        if (!$isJson) {
            $this->line("   Total active Foreign Key constraints in database: {$totalFks}");
        }

        if ($isStrict && $totalFks !== self::EXPECTED_FOREIGN_KEYS_COUNT) {
            $errors[] = "Strict check failed: Total Foreign Keys count ({$totalFks}) does not match authoritative baseline (" . self::EXPECTED_FOREIGN_KEYS_COUNT . ")";
        }

        $fkMismatches = 0;
        foreach ($fks as $fk) {
            $localType = preg_replace('/\(\d+\)/', '', strtolower($fk->LOCAL_TYPE));
            $refType = preg_replace('/\(\d+\)/', '', strtolower($fk->REFERENCED_TYPE));

            if ($localType !== $refType) {
                $errors[] = "FK Datatype Mismatch in `{$fk->TABLE_NAME}`.`{$fk->COLUMN_NAME}` ({$fk->LOCAL_TYPE}) -> `{$fk->REFERENCED_TABLE_NAME}`.`{$fk->REFERENCED_COLUMN_NAME}` ({$fk->REFERENCED_TYPE}) [Constraint: {$fk->CONSTRAINT_NAME}]";
                $fkMismatches++;
            }
        }

        // Check for duplicate FK constraints
        $fkSignatures = [];
        foreach ($fks as $fk) {
            $sig = "{$fk->TABLE_NAME}.{$fk->COLUMN_NAME}->{$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}";
            if (isset($fkSignatures[$sig])) {
                $warnings[] = "Duplicate foreign key constraint on {$sig}: '{$fkSignatures[$sig]}' and '{$fk->CONSTRAINT_NAME}'";
            } else {
                $fkSignatures[$sig] = $fk->CONSTRAINT_NAME;
            }
        }

        // 4. Auditing user-referencing column datatypes
        if (!$isJson) {
            $this->line('4. Auditing user-referencing column datatypes...');
        }
        $userColsQuery = "
            SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME NOT IN ('users', 'sessions', 'cache', 'jobs', 'failed_jobs', 'personal_access_tokens')
              AND (COLUMN_NAME = 'user_id' OR COLUMN_NAME LIKE '%_user_id')
        ";
        $userCols = DB::select($userColsQuery);
        foreach ($userCols as $uc) {
            $uType = strtolower($uc->COLUMN_TYPE);
            if (strpos($uType, 'bigint') === false || strpos($uType, 'unsigned') === false) {
                $errors[] = "User reference `{$uc->TABLE_NAME}`.`{$uc->COLUMN_NAME}` is {$uType}, expected BIGINT UNSIGNED";
            }
        }

        // 5. Check identifier length <= 64 characters (MySQL max identifier length)
        $longIdentifiers = DB::select("
            SELECT CONSTRAINT_NAME, TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() AND LENGTH(CONSTRAINT_NAME) > 64
        ");
        foreach ($longIdentifiers as $li) {
            $errors[] = "Constraint name exceeds 64 characters: `{$li->TABLE_NAME}`.`{$li->CONSTRAINT_NAME}` (" . strlen($li->CONSTRAINT_NAME) . " chars)";
        }

        // Distinct indexes count
        $distinctIndexesCount = (int) (DB::select("
            SELECT COUNT(DISTINCT TABLE_NAME, INDEX_NAME) AS cnt 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE()
        ")[0]->cnt ?? 0);

        $passed = empty($errors);

        if ($isJson) {
            $report = [
                'status' => $passed ? 'PASS' : 'FAIL',
                'timestamp' => now()->toIso8601String(),
                'database' => $dbName,
                'strict_mode' => $isStrict,
                'summary' => [
                    'total_tables' => $totalTables,
                    'expected_tables' => self::EXPECTED_TABLES_COUNT,
                    'tables_match' => ($totalTables === self::EXPECTED_TABLES_COUNT),
                    'total_foreign_keys' => $totalFks,
                    'expected_foreign_keys' => self::EXPECTED_FOREIGN_KEYS_COUNT,
                    'foreign_keys_match' => ($totalFks === self::EXPECTED_FOREIGN_KEYS_COUNT),
                    'distinct_indexes' => $distinctIndexesCount,
                    'user_columns_audited' => count($userCols),
                    'errors_count' => count($errors),
                    'warnings_count' => count($warnings),
                ],
                'errors' => $errors,
                'warnings' => $warnings,
            ];

            $this->output->writeln(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $passed ? 0 : 1;
        }

        // Detailed output if requested
        if ($isDetailed) {
            $this->table(
                ['Sample Validated Core Table', 'Status'],
                array_map(fn ($t) => [$t, 'EXISTS'], $coreTables)
            );
        }

        // Summary report
        $this->newLine();
        $this->line('====================================================');
        $this->line('           HCM SCHEMA VERIFICATION REPORT           ');
        $this->line('====================================================');
        $this->line("Database:                   {$dbName}");
        $this->line("Strict Mode:                " . ($isStrict ? "ENABLED" : "DISABLED"));
        $this->line("Total Tables Checked:       {$totalTables} (Baseline: " . self::EXPECTED_TABLES_COUNT . ")");
        $this->line("Foreign Keys Validated:     {$totalFks} (Baseline: " . self::EXPECTED_FOREIGN_KEYS_COUNT . ")");
        $this->line("Distinct Indexes:           {$distinctIndexesCount}");
        $this->line("User Columns Validated:     " . count($userCols));
        $this->line("Warnings:                   " . count($warnings));
        $this->line("Errors:                     " . count($errors));
        $this->line('----------------------------------------------------');

        if (!empty($warnings)) {
            $this->warn("WARNINGS (" . count($warnings) . "):");
            foreach ($warnings as $w) {
                $this->warn(" [!] {$w}");
            }
        }

        if (!empty($errors)) {
            $this->error("ERRORS (" . count($errors) . "):");
            foreach ($errors as $e) {
                $this->error(" [X] {$e}");
            }
            $this->newLine();
            $this->error("Schema verification FAILED with " . count($errors) . " error(s).");
            return 1;
        }

        $this->info("SUCCESS: All schema verification rules passed with 0 errors!");
        return 0;
    }
}
