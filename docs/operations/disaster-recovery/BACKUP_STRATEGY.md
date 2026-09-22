# Enterprise Backup Strategy & Cryptographic Storage Standard

## 1. Multi-Tier Backup Architecture
The platform enforces a Defense-in-Depth backup model spanning local, regional, and air-gapped immutable storage targets:

```text
Production Environment (Region A)
   │
   ├── [mysqldump --single-transaction] ──→ Local Storage (Ephemeral 24h)
   │                                          │
   │                                          ├── Encrypt (AES-256 / KMS)
   │                                          └── Compute SHA-256 Checksum
   │                                                 │
   ├── Continuous Binary Log Replication (15m)       │
   │                                                 ↓
   └── S3 Document Uploads ──────────────→ Primary S3 Bucket (Region A)
                                                     │
                                                     ├── Cross-Region Replication (CRR)
                                                     └── Object Lock (WORM Compliance)
                                                           │
                                                           ↓
                                           Secondary S3 Bucket (Region B / Air-Gapped)
```

---

## 2. Database Backup Procedures
- **Tooling:** Orchestrated via `database/scripts/backup.ps1` (Windows) and `database/scripts/backup.sh` (Linux).
- **Transactional Consistency:** Uses `--single-transaction` with InnoDB to guarantee zero table locks during backup execution.
- **Routines & Triggers:** Encapsulates `--routines --triggers --events --default-character-set=utf8mb4`.
- **Integrity Digest:** Immediately following dump completion, computes SHA-256 hash and writes to `<backup_file>.sha256`.

---

## 3. Cryptographic Storage & Immutability (WORM)
- **Encryption at Rest:** All backup archives are encrypted with AES-256 via AWS KMS or dedicated GPG public keys before being stored.
- **Write Once, Read Many (WORM):** S3 Object Lock is enabled in Compliance mode with a minimum 30-day retention lock, preventing deletion or overwrite even by root cloud credentials (protecting against ransomware and compromised administrative accounts).
- **Access Restrictions:** Backup buckets are accessible only via IAM roles granted to the automated backup runner and designated Incident Commanders.

---

## 4. Retention Schedules & Lifecycle Policies
| Classification | Backup Frequency | Active Retention | Glacier Deep Archive | Purge / Destruction |
|---|---|---|---|---|
| **Daily Operational** | Every 24h at 02:00 UTC | 14 days | 30 days | Day 31 |
| **Weekly Milestones** | Every Sunday 03:00 UTC | 30 days | 90 days | Day 91 |
| **Monthly Financial** | 1st of every month | 90 days | 12 months | Month 13 |
| **Annual Compliance** | December 31 23:59 UTC | 1 year | 7 years | Year 8 (Statutory Review) |
| **Binary Logs (WAL)** | Every 15 minutes continuous | 14 days | N/A | Day 15 |
