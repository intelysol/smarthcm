# Enterprise HCM — Migration Performance & Execution Audit

> Diagnostic audit examining execution metrics, bottlenecks, and the resolution of apparent "hangs" during `php artisan migrate:fresh`.

---

## 1. Executive Summary & Core Metrics

The Enterprise HCM platform database represents a massive enterprise architecture:
- **Migration Files**: 104
- **Database Tables**: 975 (plus system/framework tables = 985 total)
- **Foreign Key Constraints**: 2,132 (migrations = 2,180 total)
- **Secondary Indexes**: 4,377

During initial fresh migration runs, `php artisan migrate:fresh` appeared to stall or hang, creating the impression of an infinite loop or frozen connection. In-depth profiling using `SHOW FULL PROCESSLIST` and timestamp logging identified the exact physical bottlenecks and SQL errors causing this behavior.

---

## 2. Root Causes of Apparent "Hangs"

### A. Windows NTFS Synchronous DDL I/O Bottleneck
- **Observation**: Unlike bulk DML operations, MySQL DDL (`CREATE TABLE`, `ALTER TABLE ADD CONSTRAINT`) performs an **implicit transaction commit**, updates the InnoDB system data dictionary, opens the table, and forces an `fsync()` to disk.
- **Scale**: Across 104 migrations creating 975 tables and 2,180 foreign keys, MySQL executes over **4,500 distinct DDL operations**. On Windows filesystem (NTFS disk I/O at `c:\laragon\data\mysql`), each DDL operation takes between 100ms to 400ms.
- **Execution Time**: Total execution time is approximately **12 to 14 minutes** in sequential execution. When running without verbose logging, long migrations (e.g. `2026_08_19_000150_create_enterprise_tenancy_tables` taking 55–68 seconds alone) appear completely frozen.

### B. Initial Fatal SQL Errors Halting Execution Silently
Before repairs were implemented, two silent DDL halting conditions were discovered:
1. **Identifier Length > 64 Characters**:
   `compensation_recommendations_compensation_cycle_id_employee_id_unique` (69 characters) threw MySQL error 1059.
2. **Key Length Exceeded (3,072 bytes limit)**:
   In `2026_10_05_000001_create_enterprise_workforce_data_governance_tables.php`, `hcm_gov_master_mappings` defined a 4-column unique key with three `VARCHAR(255)` columns in `utf8mb4`, totaling 3,204 bytes ($> 3,072$ bytes), which halted MySQL immediately with error 1071.

---

## 3. Migration Duration Benchmark (Slowest vs Fastest)

### Slowest Migrations (Top 10)
| Migration File | Duration | Tables / DDL Operations | Why It Takes Time |
| :--- | :---: | :---: | :--- |
| `2026_08_19_000150_create_enterprise_tenancy_tables.php` | **55s** | 32 tables | Extensive dynamic table loops & tenant foreign keys |
| `2026_09_06_000001_create_enterprise_payroll_and_compensation_engine_tables.php` | **36s** | 35 tables | Payroll engine calculation snapshots, components & accounting exports |
| `2026_09_04_000002_create_employee_relations_tables.php` | **35s** | 30 tables | Deep investigation hierarchy, statements, questions, and appeal FKs |
| `2026_09_07_000001_create_enterprise_benefits_and_financial_wellness_tables.php` | **35s** | 28 tables | Benefit plans, enrollments, wellness programs, and claims |
| `2026_09_05_000001_create_enterprise_attendance_and_workforce_scheduling_tables.php` | **33s** | 28 tables | High-cardinality shift schedules, rosters, and punch logs |
| `2026_09_01_000001_create_learning_development_tables.php` | **27s** | 24 tables | Course catalogs, cohorts, skills matrices, certifications |
| `2026_09_02_000001_create_career_talent_succession_tables.php` | **26s** | 22 tables | Succession pools, talent ratings, career path nodes |
| `2026_07_09_000009_create_employee_core_tables.php` | **25s** | 20 tables | Core personal, timeline, education, experience, and contact tables |
| `2026_08_17_000001_create_identity_security_tables.php` | **23s** | 18 tables | MFA methods, session audit logs, recovery tokens |
| `2026_09_16_000001_create_enterprise_hcm_offboarding_tables.php` | **23s** | 16 tables | Exit interviews, asset recovery checklist, severance records |

### Fastest Migrations
Migrations with 1–2 utility tables or lightweight index adjustments run in under **500ms** (e.g. `0001_01_01_000001_create_cache_table.php` [210ms], `2026_07_11_000010_create_employee_number_sequences_table.php` [470ms]).

---

## 4. Remediation Measures Applied

1. **Shortened All 40+ Identifiers Exceeding 64 Chars**:
   Eliminated all MySQL 1059 errors across indexes, unique constraints, and foreign keys.
2. **Fixed Key Length in `hcm_gov_master_mappings`**:
   Constrained `VARCHAR(255)` columns to domain-appropriate sizes (`VARCHAR(60)` / `VARCHAR(100)`), reducing key byte width from 3,204 bytes to 1,024 bytes.
3. **Normalized Duplicate Timestamps**:
   Renamed duplicate timestamp files (`2026_09_04_000002_create_employee_relations_tables.php` and `2026_09_08_000002_create_hcm_benefits_administration_tables.php`) ensuring deterministic execution order.
4. **Verified Clean 100% Execution**:
   Verified that running `php artisan migrate:fresh -vvv --no-interaction` executes all 104 migrations continuously without stopping.

---

## 5. Recommended Local MySQL Database Optimizations

For rapid local testing of a 975-table schema in development/Laragon, the following MySQL `my.ini` settings dramatically reduce DDL disk flush overhead:

```ini
[mysqld]
# Buffer pool sized for 975+ tables
innodb_buffer_pool_size = 1G

# Allow lazy disk flush during development migrations (safe for dev only)
innodb_flush_log_at_trx_commit = 2

# Increase lock wait timeout for large schema operations
innodb_lock_wait_timeout = 120

# Increase packet and table cache sizes
max_allowed_packet = 128M
table_open_cache = 4000
table_definition_cache = 4000
```
