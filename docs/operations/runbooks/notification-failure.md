# Operational Runbook: Email, SMS & Push Notification Failure

## 1. Overview
- **ID:** `rb-notification-failure`
- **Severity:** SEV-2 (Significant)
- **Component:** Notification Hub / SMTP / Mailgun / SES / Twilio SMS
- **Trigger:** Notification bounce rate > 5% or SMTP gateway timeout spike.

---

## 2. Immediate Diagnostic Steps
1. **Check Email Gateway Health:**
   ```bash
   curl -s https://app.smarthcm.com/health/dependencies | jq .dependencies.mail
   ```
2. **Inspect Mail Transport Logs:**
   ```bash
   grep -i "mailer" storage/logs/laravel.log | grep -E "ERROR|CRITICAL" | tail -n 20
   ```
3. **Verify Third-Party Gateway API Quotas:**
   - Check AWS SES / Mailgun / SendGrid dashboard for reputation score, bounce thresholds, or daily sending limits.

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: SES / Mailgun Account Sandbox or Quota Exceeded**
  - Temporarily switch to secondary SMTP mailer in `.env`:
    ```ini
    MAIL_MAILER=smtp
    MAIL_HOST=smtp-backup.provider.com
    ```
  - Run `php artisan config:cache`.
- **Scenario B: Notification Queue Flooded by Non-Critical Digests**
  - Prioritize transactional auth emails (password reset, MFA code) over daily digests:
    - Route transactional mail to `queue: high`.
    - Throttle batch digests to off-peak hours.
- **Scenario C: In-App Database Notifications Failing**
  - Inspect `notifications` database table locks or slow queries.

---

## 4. Verification & Post-Resolution
- Send test notification via Artisan tinker:
  ```bash
  php artisan tinker --execute="Notification::route('mail', 'ops-test@smarthcm.com')->notify(new \App\Notifications\SystemHealthPing());"
  ```
- Verify receipt and confirm queue latency returns to < 5s.
