# Operational Runbook: Archive Cryptographic Integrity Failure

## Severity: P1 (Data Corruption / Tamper Alert)
## Service: Operations Center / Security Operations

### 1. Alert Trigger
* Scheduled background integrity scanner detects that a stored archive file hash does not match `checksum_sha256` recorded in `data_lifecycle_archives`.

### 2. Immediate Quarantine Protocol
1. Mark archive record status as `CORRUPTED_QUARANTINED`.
2. Block all restoration requests targeting this archive package.
3. Notify CISO and Security Operations of potential silent disk corruption or unauthorized object manipulation.

### 3. Verification & Restoration from Secondary Replica
1. Check offsite disaster recovery replica (cross-region S3 bucket or secondary vault):
   ```bash
   php artisan lifecycle:archive:verify-replica --archive-id={ARCHIVE_ID}
   ```
2. If replica SHA-256 matches manifest signature:
   * Restore the corrupted primary object from the verified offsite replica.
   * Re-verify primary checksum.
   * Reset status to `ARCHIVED`.
3. If replica is also degraded:
   * Escalate to DR Incident Commander for point-in-time database snapshot extraction.
