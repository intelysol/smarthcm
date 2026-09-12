# Raw Events & Idempotent Normalization

## 1. Immutability Principle
Raw biometric and API events (`attendance_raw_events`) are stored immutably. Raw payloads, received timestamps, and device identifiers are NEVER overwritten or destroyed.

## 2. Idempotency & Deduplication
Every raw event records an `idempotency_key`. Repeated transmissions from biometric terminals or web clients with identical keys return HTTP 200 without creating duplicate raw records.

## 3. Normalization Pipeline
1. Ingest into `attendance_raw_events`
2. Resolve Employee via `employee_device_identifier` (employee code, number, or UUID)
3. Resolve Timezone from terminal/device or tenant default
4. Map event direction to `NormalizedEventType` (`check_in`, `check_out`, `break_start`, `break_end`)
5. Generate deterministic normalized key and create `attendance_events` record