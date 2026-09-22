# Enterprise Test Environment Architecture & Isolation

## 1. Purpose & Scope

This document specifies the test execution environments, database isolation protocols, seed state management, and configuration profiles for running SmartHCM regression test suites locally and in CI/CD.

---

## 2. Supported Test Database Drivers

The platform supports two official test database targets:

### A. Ephemeral SQLite In-Memory (Local & Quick Verification)
- **Driver**: `sqlite`
- **Database**: `:memory:`
- **Characteristics**: Extremely fast execution (~10-30ms per test file), full in-memory destruction upon process exit.
- **Foreign Key Behavior**: Foreign keys are enforced in SQLite via PRAGMA statements during migration boot.
- **Considerations**: SQLite does not support MySQL-specific index definitions or full text search syntax without abstraction. Index name collisions across differing tables must be guarded against.

### B. Ephemeral MySQL 8.4 CI Container (Authoritative CI Certification)
- **Driver**: `mysql`
- **Database**: `smarthcm_test` (or dynamic runner schema `smarthcm_ci_<build_id>`)
- **Characteristics**: Exact byte-for-byte engine match with staging and production environments, verifies all 1,007 tables and 2,180 foreign key constraints.
- **Isolation**: Executed using transactional rollbacks or ephemeral database containers spun up on-demand per workflow job.

---

## 3. Environment Variables Configuration

For running test suites, `phpunit.xml` configures isolated environment variables:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="APP_MAINTENANCE_DRIVER" value="file"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="PULSE_ENABLED" value="false"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="TELESCOPE_ENABLED" value="false"/>
</php>
```

### Performance Optimizations:
1. `BCRYPT_ROUNDS=4`: Reduces password hashing latency by 90% during high-volume user generation.
2. `CACHE_STORE=array`: Prevents cache pollution and stale keys between test cases.
3. `QUEUE_CONNECTION=sync`: Executes queued jobs inline synchronously for deterministic assertions.
4. `MAIL_MAILER=array`: Captures outbound notifications without touching external SMTP relays.

---

## 4. Reset & Rollback Strategy

Every feature, security, and tenant test must leverage Laravel's `RefreshDatabase` trait:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyEnterpriseTest extends TestCase
{
    use RefreshDatabase;
    // ...
}
```

### Invariant Rules:
- **No Shared State**: Never rely on data seeded by an earlier test file.
- **Strict Cleanliness**: Files written during test runs must target `Storage::fake('disk_name')`. Never write files to `storage/app/public` or `/tmp` during test execution.
- **Clock Mocking**: When asserting time-sensitive workflows (probation, attendance, leave expiries), use `Carbon::setTestNow()` and restore in `tearDown()`.
