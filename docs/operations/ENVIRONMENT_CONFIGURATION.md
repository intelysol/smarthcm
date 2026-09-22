# Enterprise Platform — Environment Configuration & Secret Governance

## 1. Environment Topology Strategy
The platform formally defines four distinct deployment environments with explicit operational boundaries:

| Environment | Purpose | `APP_DEBUG` | `APP_ENV` | Caching & Queues | Storage Backend | Database Target |
|---|---|---|---|---|---|---|
| **Development** | Local engineering and feature branch development | `true` | `local` | `array` / `sync` / `file` | `local` | Local MySQL 8.4 (`smarthcm`) |
| **Testing** | CI/CD automation, unit, feature, and isolation suites | `false` | `testing` | `array` / `sync` | `array` | SQLite `:memory:` / MySQL ephemeral |
| **Staging** | Production mirror for UAT, load, and restore drills | `false` | `staging` | `redis` / Horizon | S3 staging bucket | Dedicated RDS Multi-AZ Staging |
| **Production** | Live tenant workloads & regulated operations | `false` | `production` | `redis` / Horizon | S3 production bucket | Dedicated RDS Multi-AZ Production |

---

## 2. Secret Vaulting & Cryptographic Isolation
1. **Zero Hardcoded Credentials:**
   No secret, private key, API token, or database password shall be committed to git repositories, Dockerfile layers, or code comments.
2. **Encrypted at Rest:**
   All third-party integration credentials, OAuth tokens, and client secrets stored in the database are encrypted via AES-256-GCM using Laravel's `Crypt` facade before persistence.
3. **Secret Injection in Production:**
   Production systems retrieve environment configurations at launch from centralized secret stores:
   - AWS Systems Manager Parameter Store / AWS Secrets Manager
   - HashiCorp Vault
   - Azure Key Vault
   - Kubernetes Encrypted Secrets
4. **`APP_KEY` Protection:**
   The 32-character AES-256 encryption key (`APP_KEY`) must remain immutable across application upgrades. Rotating `APP_KEY` requires executing the batch re-encryption migration utility for all encrypted database fields.

---

## 3. Configuration Caching in Production
In production environments, Laravel configuration files must be compiled into a single optimized cache artifact during deployment:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
Once `config:cache` is invoked, `env()` calls outside of `config/` files return `null`. All application code must strictly access configuration parameters via `config('key.name')`.

---

## 4. Key Security & Rotation Policies
- **Database Passwords:** Rotated every 90 days with zero-downtime dual-user rotation.
- **API Keys & Integrations:** Granular per-client rotation supported with 14-day overlap grace periods.
- **MFA Recovery Codes:** One-time use hashed using bcrypt with automatic invalidation upon use.
- **Session Keys:** 120-minute sliding window with Redis eviction.
